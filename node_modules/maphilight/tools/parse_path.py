#!/usr/local/bin/python

"""
Based on: http://wxpsvg.googlecode.com/svn/trunk/svg/pathdata.py
According to that project, this file is licensed under the LGPL
"""
try:
    from pyparsing import (Literal, Word, CaselessLiteral,
        Optional, Combine, ZeroOrMore, nums, oneOf, Group, ParseException, OneOrMore)
except ImportError:
    import sys
    sys.exit("pyparsing is required")


# ParserElement.enablePackrat()

def Command(char):
    return oneOf([char.upper(), char.lower()])


def Arguments(token):
    return Group(token)


def Sequence(token):
    """ A sequence of the token"""
    return OneOrMore(token + maybeComma)


digit_sequence = Word(nums)

sign = oneOf("+ -")


def convertToFloat(s, loc, toks):
    try:
        return float(toks[0])
    except Exception:
        raise ParseException(loc, "invalid float format %s" % toks[0])


exponent = CaselessLiteral("e") + Optional(sign) + Word(nums)

# note that almost all these fields are optional,
# and this can match almost anything. We rely on Pythons built-in
# float() function to clear out invalid values - loosely matching like this
# speeds up parsing quite a lot
floatingPointConstant = Combine(
    Optional(sign) +
    Optional(Word(nums)) +
    Optional(Literal(".") + Optional(Word(nums))) +
    Optional(exponent)
)

floatingPointConstant.setParseAction(convertToFloat)

number = floatingPointConstant

# same as FP constant but don't allow a - sign
nonnegativeNumber = Combine(
    Optional(Word(nums)) +
    Optional(Literal(".") + Optional(Word(nums))) +
    Optional(exponent)
)
nonnegativeNumber.setParseAction(convertToFloat)

coordinate = number

# comma or whitespace can seperate values all over the place in SVG
maybeComma = Optional(Literal(',')).suppress()

coordinateSequence = Sequence(coordinate)

coordinatePair = (coordinate + maybeComma + coordinate).setParseAction(lambda t: tuple(t))
coordinatePairSequence = Sequence(coordinatePair)

coordinatePairPair = coordinatePair + maybeComma + coordinatePair
coordinatePairPairSequence = Sequence(Group(coordinatePairPair))

coordinatePairTriple = coordinatePair + maybeComma + coordinatePair + maybeComma + coordinatePair
coordinatePairTripleSequence = Sequence(Group(coordinatePairTriple))

# commands
lineTo = Group(Command("L") + Arguments(coordinatePairSequence))
curve = Group(Command("C") + Arguments(coordinatePairSequence))

moveTo = Group(Command("M") + Arguments(coordinatePairSequence))

closePath = Group(Command("Z")).setParseAction(lambda t: ('Z', (None,)))

flag = oneOf("1 0").setParseAction(lambda t: bool(int((t[0]))))

arcRadius = (
    nonnegativeNumber + maybeComma +  # rx
    nonnegativeNumber  # ry
).setParseAction(lambda t: tuple(t))

arcFlags = (flag + maybeComma + flag).setParseAction(lambda t: tuple(t))

ellipticalArcArgument = Group(
    arcRadius + maybeComma +  # rx, ry
    number + maybeComma +  # rotation
    arcFlags +  # large-arc-flag, sweep-flag
    coordinatePair  # (x,y)
)

ellipticalArc = Group(Command("A") + Arguments(Sequence(ellipticalArcArgument)))

smoothQuadraticBezierCurveto = Group(Command("T") + Arguments(coordinatePairSequence))

quadraticBezierCurveto = Group(Command("Q") + Arguments(coordinatePairPairSequence))

smoothCurve = Group(Command("S") + Arguments(coordinatePairPairSequence))

# curve = Group(Command("C") + Arguments(coordinatePairTripleSequence))

horizontalLine = Group(Command("H") + Arguments(coordinateSequence))
verticalLine = Group(Command("V") + Arguments(coordinateSequence))

drawToCommand = (
    lineTo | moveTo | closePath | ellipticalArc | smoothQuadraticBezierCurveto |
    quadraticBezierCurveto | smoothCurve | curve | horizontalLine | verticalLine
)

# number.debug = True
moveToDrawToCommands = moveTo + ZeroOrMore(drawToCommand)

path = ZeroOrMore(moveToDrawToCommands)
path.keepTabs = True


def map_relative_points(points, last=(0, 0)):
    mapped = []
    for point in points:
        mapped.append((point[0] + last[0], point[1] + last[1]))
        last = mapped[-1]
    return mapped


def get_points(d):
    commands = path.parseString(d)
    points = []
    currentset = None
    for command in commands:
        if command[0].islower() and command[1]:
            # Lower-case command means "relative to previous point"
            # commandpoints = map_relative_points(command[1], currentset and currentset[-1] or (0, 0))
            commandpoints = map_relative_points(command[1], points and points[-1][-1] or (0, 0))
        else:
            # This should just be Z, which has no associated points
            commandpoints = command[1]
        if command[0] in ('M', 'm'):
            currentset = []
            points.append(currentset)
            currentset.extend(commandpoints)
        elif command[0] in ('L', 'l'):
            # L: line, sequence of points in line
            currentset.extend(commandpoints)
        elif command[0] in ('C', 'c'):
            # C: curve, control-points for bezier function, last coord is ending-point
            # TODO: actually emulate curve, rather than treating it as a line to the ending-point
            # currentset.append(commandpoints[-1])
            currentset.extend(commandpoints[0::3])
        elif command[0] in ('Z', 'z'):
            # This mostly needs to be included for the relative cases
            currentset.append(currentset[0])
        else:
            print("UNKNOWN COMMAND", command)
    return points


if __name__ == "__main__":
    #WV:
    print(get_points("m 750.01158,385.53277 1.07479,4.77904 0.9375,5.46875 0.3125,-0.3125 2.03126,-2.34375 2.1875,-2.96876 2.34375,-0.15625 1.40626,-1.40625 1.71875,-2.5 1.25,0.625 2.8125,-0.3125 2.50001,-2.03125 1.93972,-1.40463 1.78348,-0.46875 1.58931,1.09213 2.8125,1.40625 1.875,1.71875 1.32813,1.25 -1.01562,4.84376 -5.46877,-2.96876 -4.375,-1.71875 -0.15625,5.15626 -0.46875,2.03125 -1.5625,2.65626 -0.62501,1.5625 -2.96875,2.34375 -0.46875,2.18751 -3.28126,0.3125 -0.3125,2.96875 -1.09375,5.31251 -2.5,0 -1.25,-0.78125 -1.56251,-2.65626 -1.71875,0.15625 -0.3125,4.21876 -2.03125,6.40626 -4.84376,10.46877 0.78125,1.25 -0.15625,2.65625 -2.03125,1.875 -1.40625,-0.3125 -3.12501,2.34376 -2.5,-0.9375 -1.71875,4.53125 c 0,0 -3.59376,0.78125 -4.21876,0.9375 -0.625,0.15625 -2.34375,-1.25 -2.34375,-1.25 l -2.34376,2.1875 -2.5,0.62501 -2.81251,-0.78126 -1.25,-1.25 -2.11885,-2.92217 -3.03741,-1.92158 -2.5,-2.65626 -2.8125,-3.59375 -0.625,-2.18751 -2.50001,-1.40625 -0.78125,-1.5625 -0.23438,-5.07814 2.10938,-0.0781 1.87501,-0.78125 0.15625,-2.65625 1.5625,-1.40626 0.15625,-4.84375 0.9375,-3.75001 1.25,-0.625 1.25,1.09375 0.46876,1.71875 1.71875,-0.9375 0.46875,-1.5625 -1.09375,-1.71875 0,-2.34376 0.9375,-1.25 2.1875,-3.28125 1.25,-1.40625 2.03126,0.46875 2.1875,-1.56251 2.96875,-3.28125 2.18751,-3.75001 0.3125,-5.46875 0.46875,-4.84376 0,-4.53126 -1.09375,-2.96875 0.9375,-1.40626 1.24052,-1.25 3.37441,19.16354 4.47602,-0.72601 12.02179,-1.49778 z"))
    # print get_points("M 242.96145,653.59282 L 244.83646,650.1553 L 247.02397,649.8428 L 247.33647,650.62405 L 245.30521,653.59282 L 242.96145,653.59282 z M 252.80525,649.99905 L 258.74278,652.49906 L 260.77404,652.18656 L 262.33654,648.43654 L 261.71154,645.15528 L 257.64902,644.68653 L 253.74275,646.40528 L 252.80525,649.99905 z M 282.49289,659.6866 L 286.08665,664.99912 L 288.43041,664.68662 L 289.52417,664.21787 L 290.93042,665.46787 L 294.52419,665.31162 L 295.4617,663.90537 L 292.64918,662.18661 L 290.77417,658.59284 L 288.74291,655.15533 L 283.11789,657.96784 L 282.49289,659.6866 z M 302.02423,668.28039 L 303.27423,666.40538 L 307.8055,667.34288 L 308.43051,666.87413 L 314.36803,667.49913 L 314.05553,668.74914 L 311.55552,670.15539 L 307.33675,669.84289 L 302.02423,668.28039 z M 307.1805,673.28041 L 309.05551,677.03043 L 312.02427,675.93667 L 312.33677,674.37416 L 310.77427,672.3429 L 307.1805,672.0304 L 307.1805,673.28041 z M 313.89928,672.18665 L 316.08679,669.37414 L 320.61806,671.7179 L 324.83683,672.81166 L 329.0556,675.46792 L 329.0556,677.34293 L 325.61809,679.06169 L 320.93056,679.99919 L 318.5868,678.59293 L 313.89928,672.18665 z M 329.99311,687.18672 L 331.55561,685.93672 L 334.83688,687.49923 L 342.18066,690.93674 L 345.46193,692.968 L 347.02443,695.31176 L 348.89944,699.53053 L 352.80571,702.03054 L 352.49321,703.28055 L 348.74319,706.40556 L 344.68067,707.81182 L 343.27442,707.18682 L 340.30565,708.90557 L 337.96189,712.03059 L 335.77438,714.8431 L 334.05562,714.68685 L 330.61811,712.18684 L 330.30561,707.81182 L 330.93061,705.46806 L 329.3681,699.99928 L 327.33684,698.28052 L 327.18059,695.78051 L 329.3681,694.84301 L 331.39936,691.87425 L 331.86811,690.93674 L 330.30561,689.21798 L 329.99311,687.18672 z ")
    # print get_points("m 242.96145,653.59282 1.87501,-3.43752 2.18751,-0.3125 0.3125,0.78125 -2.03126,2.96877 -2.34376,0 z m 9.8438,-3.59377 5.93753,2.50001 2.03126,-0.3125 1.5625,-3.75002 -0.625,-3.28126 -4.06252,-0.46875 -3.90627,1.71875 -0.9375,3.59377 z m 29.68764,9.68755 3.59376,5.31252 2.34376,-0.3125 1.09376,-0.46875 1.40625,1.25 3.59377,-0.15625 0.93751,-1.40625 -2.81252,-1.71876 -1.87501,-3.59377 -2.03126,-3.43751 -5.62502,2.81251 -0.625,1.71876 z m 19.53134,8.59379 1.25,-1.87501 4.53127,0.9375 0.62501,-0.46875 5.93752,0.625 -0.3125,1.25001 -2.50001,1.40625 -4.21877,-0.3125 -5.31252,-1.5625 z m 5.15627,5.00002 1.87501,3.75002 2.96876,-1.09376 0.3125,-1.56251 -1.5625,-2.03126 -3.59377,-0.3125 0,1.25001 z m 6.71878,-1.09376 2.18751,-2.81251 4.53127,2.34376 4.21877,1.09376 4.21877,2.65626 0,1.87501 -3.43751,1.71876 -4.68753,0.9375 -2.34376,-1.40626 -4.68752,-6.40628 z m 16.09383,15.00007 1.5625,-1.25 3.28127,1.56251 7.34378,3.43751 3.28127,2.03126 1.5625,2.34376 1.87501,4.21877 3.90627,2.50001 -0.3125,1.25001 -3.75002,3.12501 -4.06252,1.40626 -1.40625,-0.625 -2.96877,1.71875 -2.34376,3.12502 -2.18751,2.81251 -1.71876,-0.15625 -3.43751,-2.50001 -0.3125,-4.37502 0.625,-2.34376 -1.56251,-5.46878 -2.03126,-1.71876 -0.15625,-2.50001 2.18751,-0.9375 2.03126,-2.96876 0.46875,-0.93751 -1.5625,-1.71876 -0.3125,-2.03126 z")

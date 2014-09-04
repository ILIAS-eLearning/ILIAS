# updateLangFile.py

HEADERLINES = [
'/* Copyright (c) 1998-2013 ILIAS open source e-Learning e.V., Extended GPL, see docs/LICENSE */',
'/**',
'* ILIAS language file (All entries)',
'*',
'* @module			language file German',
'* @modulegroup		language',
'* @author			Matthias Kunkel <mkunkel@me.com>',
'* @version			$Id: ilias_de.lang 50042 2014-05-14 11:58:32Z rklees $',
'*/',
'// The language file starts beyond the HTML-comment below. DO NOT modify this line!',
'// To edit your language file with a spreadsheet (i.e. Excel or StarCalc) remove all lines',
'// from the first line to the HTML-comment. After editing paste the lines in place again.',
'// NOTICE: Character coding of all ILIAS lang files is UTF-8! Please set your editor',
'// to the corresponding mode!',
'// Language file names refer to ISO 639, see: http://www.oasis-open.org/cover/iso639a.html',
'<!-- language file start -->',
]

BASEFILE = "Customizing/global/lang/ilias_de.lang.local"
SOURCEFILE = "Customizing/global/lang/import.lang.csv"
TARGETFILE = "Customizing/global/lang/ilias_de.lang.local.new"

BUFFER = []


from pdb import set_trace as trace

class Lang:
	srcfile = ''
	outfile =''
	ldict = {}

	def __init__(self, src):
		""" """
		self.ldict = {}
		self.srcfile = src
		self.outfile =''
		self.readFile()
		

	def readFile(self):
		""" """
		

		f = open(self.srcfile, 'r')
		ls = f.readlines()
		f.close()

		for l in ls:
			if l[0] not in ['/', '<', '*']:
				split = l.split('#:#')
				try:
					lEntry = LangEntry(split[0], split[1], split[2])
				except:
					print('wrong format: %s' % l)
				
				if(self.ldict.has_key(lEntry.getId())):
					BUFFER.append('double Entry in source: %s \n' % lEntry.getId())
				self.ldict[lEntry.getId()] = lEntry

		BUFFER.append('%s  (%i Entries)\n' % (self.srcfile, len(ls)))


	def writeFile(self):
		""" """
		lines = []
		for entry in HEADERLINES:
			lines.append('%s\n' % entry)

		keylist = self.ldict.keys()
		keylist.sort()

		for id in keylist:
			entry = self.ldict[id]
			lines.append('%s\n' % entry.getLine())

		BUFFER.append('-----\n')
		BUFFER.append('writing %i lines\n' % len(lines))

		f = open(self.outfile, 'w')
		f.writelines(lines)
		f.close()

		f = open('langImport.log', 'w')
		f.writelines(BUFFER)
		f.close()



	def printFile(self):
		""" """
		for entry in self.ldict.values():
			print entry


	def update(self, lnew):
		""" """
		for entry in lnew.ldict.values():
			## msgs here (new, changed...)
			eId = entry.getId()

			if eId in self.ldict.keys():
				if self.ldict[eId].text == entry.text:
					#print 'same: %s' % eId
					pass
				else:
					BUFFER.append('text change: %s' % entry.getLine())
			else:
				BUFFER.append('new entry: %s' % entry.getLine())

			
			self.ldict[eId] = entry




class LangEntry:

	id = ''
	module = ''
	identifier = ''
	text = ''

	def __init__(self, module, identifier, text):
		""" """
		self.module = module.strip()
		self.identifier = identifier.strip()
		self.text = text.strip()
		self.id = self.getId()

	def getLine(self):
		""" """
		return '%s#:#%s#:#%s' % (self.module, self.identifier, self.text)

	def __str__(self):
		""" """
		return '<LEntry> %s' % self.getLine()

	def getId(self):
		""" """
		id = '%s#:#%s' % (self.module, self.identifier)
		return id





fbase = Lang(BASEFILE)
fnew = Lang(SOURCEFILE)

fbase.update(fnew)
fbase.outfile = TARGETFILE
fbase.writeFile()

for i in BUFFER:
	print i
print 'done'
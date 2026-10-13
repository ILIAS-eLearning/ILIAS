/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

(function (window, document) {
  const CONTAINER_SELECTOR = '[data-lso-learning-map]';
  const DEFAULT_METRICS = {
    nodeWidth: 190, nodeHeight: 108, hGap: 26, vGap: 96,
  };
  const DUMMY_WIDTH = 18;
  const MIN_HEIGHT = 280;
  const MAX_HEIGHT = 780;
  const PAD_Y = 24;

  const instances = {};

  /**
     * Escapes text for use in generated markup.
     */

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = (value === undefined || value === null) ? '' : String(value);
    return div.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /**
     * Formats translated labels with positional placeholders.
     */

  function fmt(template, args) {
    let i = 0;
    return String(template === undefined ? '' : template)
      .replace(/%(\d+)\$s|%s/g, (match, position) => {
        if (position) {
          return String(args[position - 1]);
        }
        const value = args[i];
        i += 1;
        return String(value);
      });
  }

  /**
     * Creates and registers a learning map in a container.
     */

  function create(root, data) {
    const viewport = root.querySelector('.lso-learning-map__viewport');
    const canvas = root.querySelector('.lso-learning-map__canvas');
    const svg = root.querySelector('.lso-learning-map__edges');
    const layer = root.querySelector('.lso-learning-map__nodes');
    if (!viewport || !canvas || !svg || !layer) {
      return null;
    }

    const { id } = root;
    const rawMetrics = data.metrics || {};
    const rawLabels = data.labels || {};
    const M = {
      nodeWidth: rawMetrics.node_width || DEFAULT_METRICS.nodeWidth,
      nodeHeight: rawMetrics.node_height || DEFAULT_METRICS.nodeHeight,
      hGap: rawMetrics.h_gap || DEFAULT_METRICS.hGap,
      vGap: rawMetrics.v_gap || DEFAULT_METRICS.vGap,
    };
    const labels = {
      ...rawLabels,
      openObject: rawLabels.open_object,
    };
    const glyphs = data.glyphs || {};

    const horizontal = (data.orientation === 'horizontal');
    const ALONG = horizontal ? M.nodeHeight : M.nodeWidth;
    const DEPTH = horizontal ? M.nodeWidth : M.nodeHeight;
    if (horizontal) {
      root.classList.add('lso-learning-map--horizontal');
    }

    root.style.setProperty('--lso-learning-map-node-width', `${M.nodeWidth}px`);
    root.style.setProperty('--lso-learning-map-node-height', `${M.nodeHeight}px`);

    const nodes = {};
    const order = [];
    (data.nodes || []).forEach((n) => {
      nodes[n.id] = {
        id: n.id, raw: n, out: [], in: [], rank: 0, x: 0, y: 0, sx: 0, sy: 0, dummy: false,
      };
      order.push(n.id);
    });
    if (order.length === 0) {
      return null;
    }
    const edges = (data.edges || []).filter((e) => nodes[e.from] && nodes[e.to]);
    edges.forEach((e) => {
      nodes[e.from].out.push(e);
      nodes[e.to].in.push(e);
      (nodes[e.from].raw.outEdges = nodes[e.from].raw.outEdges || []).push(e);
    });

    /**
         * Marks edges closing a cycle so the ranking always works on a DAG.
         */
    function markBackEdges() {
      const state = {};
      const roots = order.filter((k) => nodes[k].in.length === 0);
      const starts = roots.concat(order.filter((k) => nodes[k].in.length !== 0));
      edges.forEach((e) => {
        e.cycle = false;
      });
      starts.forEach((start) => {
        if (state[start]) {
          return;
        }
        const stack = [{ id: start, i: 0 }];
        state[start] = 1;
        while (stack.length) {
          const frame = stack[stack.length - 1];
          const node = nodes[frame.id];
          if (frame.i >= node.out.length) {
            state[frame.id] = 2;
            stack.pop();
          } else {
            const e = node.out[frame.i];
            frame.i += 1;
            if (state[e.to] === 1) {
              e.cycle = true;
            } else if (!state[e.to]) {
              state[e.to] = 1;
              stack.push({ id: e.to, i: 0 });
            }
          }
        }
      });
    }

    function rank() {
      markBackEdges();
      const forward = edges.filter((e) => !e.cycle);
      const indeg = {};
      const queue = [];
      order.forEach((k) => {
        indeg[k] = 0;
      });
      forward.forEach((e) => {
        indeg[e.to] += 1;
      });
      order.forEach((k) => {
        if (indeg[k] === 0) {
          queue.push(k);
        }
      });
      const outgoing = {};
      forward.forEach((e) => {
        (outgoing[e.from] = outgoing[e.from] || []).push(e);
      });
      let guard = 0;
      while (queue.length && guard < 100000) {
        guard += 1;
        const current = queue.shift();
        const out = outgoing[current] || [];
        for (let i = 0; i < out.length; i += 1) {
          const t = out[i].to;
          nodes[t].rank = Math.max(nodes[t].rank, nodes[current].rank + 1);
          indeg[t] -= 1;
          if (indeg[t] === 0) {
            queue.push(t);
          }
        }
      }
      let max = 0;
      order.forEach((k) => {
        max = Math.max(max, nodes[k].rank);
      });
      order.forEach((k) => {
        if (nodes[k].raw.terminal === 'end') {
          nodes[k].rank = max;
        }
        if (nodes[k].raw.terminal === 'start') {
          nodes[k].rank = 0;
        }
      });

      const indegForward = {};
      const outdegForward = {};
      order.forEach((k) => {
        indegForward[k] = 0;
        outdegForward[k] = 0;
      });
      forward.forEach((e) => {
        indegForward[e.to] += 1;
        outdegForward[e.from] += 1;
      });
      const detached = order.filter((k) => !nodes[k].raw.terminal
                && indegForward[k] === 0 && outdegForward[k] === 0);
      if (detached.length) {
        detached.forEach((k) => {
          nodes[k].rank = max + 1;
        });
      }
    }

    const layers = [];
    const segments = [];

    /**
         * Builds layout layers and routing segments.
         */
    function build() {
      let max = 0;
      order.forEach((k) => {
        max = Math.max(max, nodes[k].rank);
      });
      for (let r = 0; r <= max; r += 1) {
        layers[r] = [];
      }
      order.forEach((k) => {
        layers[nodes[k].rank].push(nodes[k]);
      });

      let dc = 0;
      edges.forEach((e) => {
        const a = nodes[e.from];
        const b = nodes[e.to];
        if (b.rank <= a.rank) {
          return;
        }
        if (b.rank - a.rank <= 1) {
          segments.push({ edge: e, from: a, to: b });
          return;
        }
        let prev = a;
        for (let r = a.rank + 1; r < b.rank; r += 1) {
          dc += 1;
          const d = {
            id: `dummy${dc}`, rank: r, x: 0, y: 0, sx: 0, sy: 0, dummy: true, raw: {}, in: [], out: [],
          };
          layers[r].push(d);
          segments.push({ edge: e, from: prev, to: d });
          prev = d;
        }
        segments.push({ edge: e, from: prev, to: b });
      });
      segments.forEach((s) => {
        s.from.out.push({ from: s.from.id, to: s.to.id, seg: s });
        s.to.in.push({ from: s.from.id, to: s.to.id, seg: s });
      });
    }

    let adjUp = {};
    let adjDown = {};

    /**
         * Builds adjacency indexes for graph traversal.
         */
    function buildAdj() {
      adjUp = {};
      adjDown = {};
      segments.forEach((s) => {
        (adjDown[s.from.id] = adjDown[s.from.id] || []).push(s.to);
        (adjUp[s.to.id] = adjUp[s.to.id] || []).push(s.from);
      });
    }

    function neighbours(node, dir) {
      return (dir === 'up' ? adjUp[node.id] : adjDown[node.id]) || [];
    }

    function reindex() {
      layers.forEach((l) => {
        l.forEach((n, i) => {
          n.idx = i;
        });
      });
    }

    function crossings(r) {
      if (r < 0 || r + 1 >= layers.length) {
        return 0;
      }
      const idx = {};
      layers[r + 1].forEach((n, i) => {
        idx[n.id] = i;
      });
      let list = [];
      layers[r].forEach((n) => {
        const ns = [];
        (adjDown[n.id] || []).forEach((m) => {
          if (idx[m.id] !== undefined) {
            ns.push(idx[m.id]);
          }
        });
        ns.sort((a, b) => a - b);
        list = list.concat(ns);
      });
      let c = 0;
      for (let i = 0; i < list.length; i += 1) {
        for (let j = i + 1; j < list.length; j += 1) {
          if (list[i] > list[j]) {
            c += 1;
          }
        }
      }
      return c;
    }

    function totalCrossings() {
      let t = 0;
      for (let r = 0; r < layers.length; r += 1) {
        t += crossings(r);
      }
      return t;
    }

    function median(node, dir) {
      const ns = neighbours(node, dir).map((m) => m.idx);
      ns.sort((a, b) => a - b);
      if (!ns.length) {
        return -1;
      }
      const m = Math.floor(ns.length / 2);
      if (ns.length % 2 === 1) {
        return ns[m];
      }
      if (ns.length === 2) {
        return (ns[0] + ns[1]) / 2;
      }
      const left = ns[m - 1] - ns[0];
      const right = ns[ns.length - 1] - ns[m];
      return (left + right) === 0 ? ns[m] : (ns[m - 1] * right + ns[m] * left) / (left + right);
    }

    function wmedian(dir) {
      for (let i = 0; i < layers.length; i += 1) {
        const r = (dir === 'up') ? i : layers.length - 1 - i;
        layers[r].forEach((n) => {
          const m = median(n, dir);
          n.med = (m < 0) ? n.idx : m;
        });
        layers[r].sort((a, b) => a.med - b.med);
        reindex();
      }
    }

    function transpose() {
      let improved = true;
      let guard = 0;
      while (improved && guard < 12) {
        guard += 1;
        improved = false;
        for (let r = 0; r < layers.length; r += 1) {
          const l = layers[r];
          for (let i = 0; i + 1 < l.length; i += 1) {
            const before = crossings(r - 1) + crossings(r);
            let tmp = l[i];
            l[i] = l[i + 1];
            l[i + 1] = tmp;
            reindex();
            if (crossings(r - 1) + crossings(r) < before) {
              improved = true;
            } else {
              tmp = l[i];
              l[i] = l[i + 1];
              l[i + 1] = tmp;
              reindex();
            }
          }
        }
      }
    }

    function snapshot() {
      return layers.map((l) => l.slice());
    }

    function restore(s) {
      for (let r = 0; r < layers.length; r += 1) {
        layers[r] = s[r];
      }
    }

    function ordering() {
      buildAdj();
      reindex();
      let best = snapshot();
      let bestC = totalCrossings();
      const rounds = segments.length > 600 ? 4 : 12;
      for (let it = 0; it < rounds && bestC > 0; it += 1) {
        wmedian(it % 2 === 0 ? 'up' : 'down');
        transpose();
        const c = totalCrossings();
        if (c < bestC) {
          bestC = c;
          best = snapshot();
        }
      }
      restore(best);
      reindex();
    }

    let width = 0;
    let height = 0;
    let alongExtent = 0;
    let depthExtent = 0;
    let contentTop = 0;
    let contentBottom = 0;
    const backwardRoutes = new Map();
    const backwardBounds = { left: 0, right: 0 };
    // Kept in the same order of magnitude as the aisle step used for the
    // horizontal parts of these routes (see `aisle` in backwardPolyline,
    // 8px per lane) — before this was M.hGap + 12 (~38px), roughly 5x
    // wider than the horizontal spacing, which is why parallel vertical
    // line segments looked far more spread out than parallel horizontal
    // ones even though both represent one "lane" of separation.
    const BACKWARD_LANE_SPACING = 16;
    const BACKWARD_SEARCH_LIMIT = 12;

    /**
         * Orthogonal route of a backward edge: the line leaves the object through a
         * free channel beside it, runs in the aisle between two layers and only
         * then goes outwards. Each lane uses its own aisle height, so lines
         * starting at the same object height never overlap each other.
         */
    function backwardPolyline(edge, route, bounds) {
      const source = nodes[edge.from];
      const target = nodes[edge.to];
      const dir = route.side === 'right' ? 1 : -1;
      const laneX = route.side === 'right'
        ? bounds.right + M.hGap + ((route.lane + 1) * BACKWARD_LANE_SPACING)
        : bounds.left - M.hGap - ((route.lane + 1) * BACKWARD_LANE_SPACING);
      const portY = function (node, port) {
        return node.y + ((DEPTH * (port.index + 1)) / (port.count + 1));
      };
      const channelX = function (node, port) {
        const border = route.side === 'right' ? node.x + ALONG : node.x;
        return border + (dir * ((M.hGap * (port.index + 1)) / (port.count + 1)));
      };
      const aisle = Math.max(4, Math.min(M.vGap - 6, 6 + (route.lane * 8)));
      const sourceY = portY(source, route.sourcePort);
      const targetY = portY(target, route.targetPort);
      const sourceX = route.side === 'right' ? source.x + ALONG : source.x;
      const targetX = route.side === 'right' ? target.x + ALONG : target.x;
      const sourceChannel = channelX(source, route.sourcePort);
      const targetChannel = channelX(target, route.targetPort);
      const sourceRun = source.y + DEPTH + aisle;
      const targetRun = target.y - aisle;
      const blocked = function (y, xa, xb) {
        const from = Math.min(xa, xb);
        const to = Math.max(xa, xb);
        let hit = false;
        layers.forEach((l) => {
          l.forEach((n) => {
            if (hit || n.dummy || n === source || n === target) {
              return;
            }
            if (y <= n.y || y >= n.y + DEPTH) {
              return;
            }
            if (n.x + ALONG > from && n.x < to) {
              hit = true;
            }
          });
        });
        return hit;
      };
      const head = blocked(sourceY, sourceX, laneX)
        ? [
          [sourceX, sourceY],
          [sourceChannel, sourceY],
          [sourceChannel, sourceRun],
          [laneX, sourceRun],
        ]
        : [[sourceX, sourceY], [laneX, sourceY]];
      const tail = blocked(targetY, targetX, laneX)
        ? [
          [laneX, targetRun],
          [targetChannel, targetRun],
          [targetChannel, targetY],
          [targetX, targetY],
        ]
        : [[laneX, targetY], [targetX, targetY]];
      return head.concat(tail);
    }

    function positions() {
      backwardRoutes.clear();
      const w = function (n) {
        return n.dummy ? DUMMY_WIDTH : ALONG;
      };
      layers.forEach((l, r) => {
        let total = 0;
        l.forEach((n) => {
          total += w(n) + M.hGap;
        });
        total -= M.hGap;
        let x = -total / 2;
        l.forEach((n) => {
          n.x = x;
          n.y = r * (DEPTH + M.vGap);
          x += w(n) + M.hGap;
        });
      });
      for (let it = 0; it < 6; it += 1) {
        for (let r = 0; r < layers.length; r += 1) {
          layers[r].forEach((n) => {
            const ns = neighbours(n, 'up').concat(neighbours(n, 'down'));
            if (!ns.length) {
              return;
            }
            const target = ns.reduce((a, m) => a + m.x + (w(m) / 2), 0) / ns.length - (w(n) / 2);
            n.x += (target - n.x) * 0.5;
          });
          const l = layers[r];
          for (let i = 1; i < l.length; i += 1) {
            const min = l[i - 1].x + w(l[i - 1]) + M.hGap;
            if (l[i].x < min) {
              l[i].x = min;
            }
          }
          for (let j = l.length - 2; j >= 0; j -= 1) {
            const max = l[j + 1].x - w(l[j]) - M.hGap;
            if (l[j].x > max) {
              l[j].x = max;
            }
          }
        }
      }
      let minX = Infinity;
      layers.forEach((l) => {
        l.forEach((n) => {
          minX = Math.min(minX, n.x);
        });
      });
      const backwardEdges = edges.filter((e) => nodes[e.to].rank <= nodes[e.from].rank);
      const nodeList = [];
      layers.forEach((l) => {
        l.forEach((n) => {
          if (!n.dummy) {
            nodeList.push(n);
          }
        });
      });
      const contentLeft = minX;
      let contentRight = -Infinity;
      layers.forEach((l) => {
        l.forEach((n) => {
          contentRight = Math.max(contentRight, n.x + w(n));
        });
      });

      function portOrder(port, routes) {
        const { lane } = routes.get(port.edge);
        return nodes[port.other].y > nodes[port.node].y ? -lane : lane;
      }

      function planRoutes(sides) {
        const plan = new Map();
        const counts = { left: 0, right: 0 };
        // Vertical margin kept around a lane's occupied y-range so two
        // routes sharing a lane never visually touch, even though they
        // don't overlap.
        const LANE_MARGIN = DEPTH * 0.4;
        ['left', 'right'].forEach((side) => {
          const sideEdges = backwardEdges.filter((unused, index) => sides[index] === side);
          sideEdges.sort((first, second) => {
            const firstSpan = Math.abs(nodes[first.from].y - nodes[first.to].y);
            const secondSpan = Math.abs(nodes[second.from].y - nodes[second.to].y);
            return firstSpan - secondSpan;
          });
          // Reuse a lane whenever an edge's y-range doesn't overlap the
          // range already occupied by that lane, instead of handing every
          // backward edge its own lane. This is the same idea already used
          // for the forward-edge gap lanes further down (interval reuse),
          // just applied to the side channels.
          const laneRanges = [];
          sideEdges.forEach((edge) => {
            const lo = Math.min(nodes[edge.from].y, nodes[edge.to].y);
            const hi = Math.max(nodes[edge.from].y, nodes[edge.to].y);
            let lane = laneRanges.findIndex(
              (r) => hi + LANE_MARGIN < r.lo || lo - LANE_MARGIN > r.hi,
            );
            if (lane === -1) {
              lane = laneRanges.length;
              laneRanges.push({ lo, hi });
            } else {
              laneRanges[lane].lo = Math.min(laneRanges[lane].lo, lo);
              laneRanges[lane].hi = Math.max(laneRanges[lane].hi, hi);
            }
            plan.set(edge, { side, lane });
            counts[side] = Math.max(counts[side], lane + 1);
          });
        });
        const ports = {};
        plan.forEach((route, edge) => {
          const sourceKey = `${edge.from}:${route.side}`;
          const targetKey = `${edge.to}:${route.side}`;
          (ports[sourceKey] = ports[sourceKey] || []).push({
            edge, kind: 'source', node: edge.from, other: edge.to,
          });
          (ports[targetKey] = ports[targetKey] || []).push({
            edge, kind: 'target', node: edge.to, other: edge.from,
          });
        });
        Object.keys(ports).forEach((key) => {
          ports[key].sort((first, second) => portOrder(first, plan) - portOrder(second, plan));
          ports[key].forEach((port, index, all) => {
            plan.get(port.edge)[`${port.kind}Port`] = { index, count: all.length };
          });
        });
        return { plan, counts };
      }

      function crossesLines(a, b, c, d) {
        const turn = function (p, q, r) {
          return ((q[0] - p[0]) * (r[1] - p[1])) - ((q[1] - p[1]) * (r[0] - p[0]));
        };
        const first = turn(a, b, c);
        const second = turn(a, b, d);
        const third = turn(c, d, a);
        const fourth = turn(c, d, b);
        return ((first > 0 && second < 0) || (first < 0 && second > 0))
                    && ((third > 0 && fourth < 0) || (third < 0 && fourth > 0));
      }

      function hitsNodes(a, b) {
        return nodeList.reduce((count, node) => {
          const left = node.x;
          const right = node.x + ALONG;
          const top = node.y;
          const bottom = node.y + DEPTH;
          const inside = function (p) {
            return p[0] > left && p[0] < right && p[1] > top && p[1] < bottom;
          };
          const borders = [
            [[left, top], [right, top]],
            [[right, top], [right, bottom]],
            [[right, bottom], [left, bottom]],
            [[left, bottom], [left, top]],
          ];
          const touched = inside(a) || inside(b)
                        || borders.some((border) => crossesLines(a, b, border[0], border[1]));
          return count + (touched ? 1 : 0);
        }, 0);
      }

      const forwardLines = [];
      segments.forEach((s) => {
        if (backwardEdges.indexOf(s.edge) !== -1) {
          return;
        }
        const fromX = s.from.x + (w(s.from) / 2);
        const toX = s.to.x + (w(s.to) / 2);
        const gapTop = s.from.y + DEPTH;
        const gapBottom = s.to.y;
        const middle = (gapTop + gapBottom) / 2;
        forwardLines.push([[fromX, gapTop], [fromX, middle]]);
        forwardLines.push([[fromX, middle], [toX, middle]]);
        forwardLines.push([[toX, middle], [toX, gapBottom]]);
      });
      layers.forEach((l) => {
        l.forEach((n) => {
          if (!n.dummy) {
            return;
          }
          const x = n.x + (DUMMY_WIDTH / 2);
          forwardLines.push([[x, n.y], [x, n.y + DEPTH]]);
        });
      });

      function evaluateSides(sides) {
        const planned = planRoutes(sides);
        const polylines = [];
        planned.plan.forEach((route, edge) => {
          polylines.push(backwardPolyline(edge, route, {
            left: contentLeft,
            right: contentRight,
          }));
        });
        let objectHits = 0;
        let lineCrossings = 0;
        polylines.forEach((points, index) => {
          for (let i = 1; i < points.length; i += 1) {
            const a = points[i - 1];
            const b = points[i];
            objectHits += hitsNodes(a, b);
            lineCrossings += forwardLines.reduce((count, line) => (
              count + (crossesLines(a, b, line[0], line[1]) ? 1 : 0)
            ), 0);
            for (let other = 0; other < index; other += 1) {
              const others = polylines[other];
              for (let j = 1; j < others.length; j += 1) {
                if (crossesLines(a, b, others[j - 1], others[j])) {
                  lineCrossings += 1;
                }
              }
            }
          }
        });
        const balance = Math.abs(planned.counts.right - planned.counts.left);
        return {
          score: (objectHits * 1000) + (lineCrossings * 10) + balance,
          plan: planned.plan,
          counts: planned.counts,
        };
      }

      let best = null;
      if (backwardEdges.length > 0 && backwardEdges.length <= BACKWARD_SEARCH_LIMIT) {
        const combinations = 2 ** backwardEdges.length;
        for (let combination = 0; combination < combinations; combination += 1) {
          const sides = backwardEdges.map((unused, index) => (
            Math.floor(combination / (2 ** index)) % 2 === 1 ? 'right' : 'left'
          ));
          const candidate = evaluateSides(sides);
          if (!best || candidate.score < best.score) {
            best = candidate;
          }
        }
      } else if (backwardEdges.length > 0) {
        best = evaluateSides(backwardEdges.map((unused, index) => (
          index % 2 === 0 ? 'right' : 'left'
        )));
      }
      const lanes = { left: 0, right: 0 };
      if (best) {
        best.plan.forEach((route, edge) => {
          backwardRoutes.set(edge, route);
        });
        lanes.left = best.counts.left;
        lanes.right = best.counts.right;
      }
      const leftReserve = lanes.left > 0
        ? M.hGap + (lanes.left * BACKWARD_LANE_SPACING) + 20
        : 0;
      const shift = 40 - minX + leftReserve;
      const vReserve = backwardRoutes.size > 0 ? M.vGap : 0;
      layers.forEach((l) => {
        l.forEach((n) => {
          n.x += shift;
          n.y += 20 + vReserve;
        });
      });
      let right = 0;
      let bottom = 0;
      layers.forEach((l) => {
        l.forEach((n) => {
          right = Math.max(right, n.x + w(n));
          bottom = Math.max(bottom, n.y + DEPTH);
        });
      });
      backwardBounds.left = contentLeft + shift;
      backwardBounds.right = right;
      alongExtent = right + 40
                + (lanes.right > 0 ? M.hGap + (lanes.right * BACKWARD_LANE_SPACING) : 0);
      depthExtent = bottom + 20 + vReserve;
    }

    function toScreen() {
      contentTop = Infinity;
      contentBottom = -Infinity;
      layers.forEach((l) => {
        l.forEach((n) => {
          n.sx = horizontal ? n.y : n.x;
          n.sy = horizontal ? n.x : n.y;
          if (n.dummy) {
            return;
          }
          contentTop = Math.min(contentTop, n.sy);
          contentBottom = Math.max(contentBottom, n.sy + M.nodeHeight);
        });
      });
      if (!Number.isFinite(contentTop)) {
        contentTop = 20;
        contentBottom = 20 + M.nodeHeight;
      }
      width = horizontal ? depthExtent : alongExtent;
      height = horizontal ? alongExtent : depthExtent;
    }

    function point(p) {
      return horizontal ? [p[1], p[0]] : [p[0], p[1]];
    }

    const live = root.querySelector('[data-lso-learning-map-live]');
    const summary = root.querySelector('[data-lso-learning-map-summary]');

    let announcementsOn = false;

    function announce(message) {
      if (live && announcementsOn) {
        live.textContent = message;
      }
    }

    function silently(action) {
      const wasOn = announcementsOn;
      announcementsOn = false;
      action();
      announcementsOn = wasOn;
    }

    function titleOf(nodeId) {
      return (nodes[nodeId] && nodes[nodeId].raw.title) || '';
    }

    function inLearningOrder() {
      const ordered = [];
      layers.forEach((l) => {
        l.forEach((n) => {
          if (!n.dummy) {
            ordered.push(n);
          }
        });
      });
      return ordered.length ? ordered : order.map((k) => nodes[k]);
    }

    function stateLabel(o) {
      if (o.state === 'done') {
        return labels.sr_state_done;
      }
      if (o.state === 'blocked') {
        return labels.sr_state_blocked;
      }
      return labels.sr_state_open;
    }

    function successorSentence(node) {
      const parts = [];
      (node.raw.outEdges || []).forEach((e) => {
        const name = titleOf(e.to);
        if (!name) {
          return;
        }
        parts.push(e.passable === false ? fmt(labels.sr_blocked_way, [name]) : name);
      });
      if (!parts.length) {
        return labels.sr_leads_to_none;
      }
      return fmt(labels.sr_leads_to, [parts.join(', ')]);
    }

    function nodeDescription(node, position, total) {
      const o = node.raw;
      const parts = [fmt(labels.sr_step, [position, total]), stateLabel(o)];
      if (o.current) {
        parts.push(labels.sr_current);
      }
      if (o.terminal === 'start') {
        parts.push(labels.sr_start);
      }
      if (o.terminal === 'end') {
        parts.push(labels.sr_end);
      }
      parts.push(successorSentence(node));
      return parts.join('. ').replace(/\.\./g, '.');
    }

    function writeSummary() {
      if (!summary) {
        return;
      }
      const ordered = inLearningOrder();
      const parts = [fmt(labels.sr_summary, [ordered.length])];
      let current = null;
      let first = null;
      let last = null;
      ordered.forEach((n) => {
        if (n.raw.current) {
          current = n;
        }
        if (n.raw.terminal === 'start' && !first) {
          first = n;
        }
        if (n.raw.terminal === 'end') {
          last = n;
        }
      });
      if (first) {
        parts.push(fmt(labels.sr_summary_start, [first.raw.title]));
      }
      if (last) {
        parts.push(fmt(labels.sr_summary_end, [last.raw.title]));
      }
      if (current) {
        parts.push(fmt(labels.sr_summary_current, [current.raw.title]));
      }
      summary.textContent = parts.join(' ');
    }

    function drawNodes() {
      const ordered = inLearningOrder();
      const total = ordered.length;
      let html = '';
      ordered.forEach((n, position) => {
        const o = n.raw;
        const cls = ['lso-learning-map__node'];
        if (o.state === 'done') {
          cls.push('lso-learning-map__node--done');
        }
        if (o.state === 'open') {
          cls.push('lso-learning-map__node--open');
        }
        if (o.state === 'blocked') {
          cls.push('lso-learning-map__node--blocked');
        }
        if (o.current) {
          cls.push('lso-learning-map__node--current');
        }
        if (o.terminal) {
          cls.push('lso-learning-map__node--terminal');
        }
        const stateKey = o.state === 'done' || o.state === 'blocked' ? o.state : 'open';
        const stateKeys = o.current ? ['current', stateKey] : [stateKey];
        const glyphMarkup = stateKeys
          .filter((key) => glyphs[key])
          .map((key) => `<span class="lso-learning-map__glyph lso-learning-map__glyph--${key}">${
            glyphs[key]}</span>`)
          .join('');
        const nodeGlyphs = glyphMarkup
          ? `<span class="lso-learning-map__glyphs">${glyphMarkup}</span>`
          : '';
        const descId = `${id}_n_${o.id}_desc`;
        const overlay = o.href
          ? `<a class="lso-learning-map__node-link-overlay" href="${escapeHtml(o.href)}"`
                    + ' draggable="false"'
                    + ` aria-label="${escapeHtml(`${labels.openObject}: ${o.title}`)}"`
                    + ` aria-describedby="${escapeHtml(descId)}"></a>`
          : '';

        html += `<li class="${cls.join(' ')}" id="${escapeHtml(`${id}_n_${o.id}`)}"
  style="left:${Math.round(n.sx)}px;top:${Math.round(n.sy)}px"
  data-tooltip="${escapeHtml(o.title)}">
  ${overlay}
  <span class="lso-learning-map__sr-only" id="${escapeHtml(descId)}">
    ${escapeHtml(nodeDescription(n, position + 1, total))}
  </span>
  <div class="lso-learning-map__node-body">
    <div class="lso-learning-map__node-head">
      ${
  o.icon
    ? `<img class="lso-learning-map__node-icon" src="${escapeHtml(o.icon)}" alt="">`
    : ''
  }
      <h3 class="lso-learning-map__node-title">${escapeHtml(o.title)}</h3>
    </div>
    <div class="lso-learning-map__node-desc">${escapeHtml(o.description || '')}</div>
    ${nodeGlyphs ? `<div class="lso-learning-map__node-foot">${nodeGlyphs}</div>` : ''}
  </div>
</li>`;
      });
      layer.innerHTML = html;
      writeSummary();
    }

    function drawEdges() {
      const ARROW_SIZE = 12;
      const parts = [];
      let defs = '';
      ['open', 'blocked', 'path'].forEach((key) => {
        defs += `<marker id="${id}_arrow_${key}" viewBox="0 0 10 10" refX="0" refY="5"`
                    + ` markerWidth="${ARROW_SIZE}" markerHeight="${ARROW_SIZE}" markerUnits="userSpaceOnUse"`
                    + ' orient="auto-start-reverse">'
                    + `<path class="lso-learning-map__arrow${
                      key === 'open' ? '' : ` lso-learning-map__arrow--${key}`
                    }" d="M 0 0 L 10 5 L 0 10 z"/></marker>`;
      });
      parts.push(`<defs>${defs}</defs>`);

      function edgeKind(e) {
        if (e.passable === false) {
          return 'blocked';
        }
        if (e.on_path) {
          return 'path';
        }
        return 'open';
      }

      function edgeClass(kind) {
        return `lso-learning-map__edge${kind === 'blocked' ? ' lso-learning-map__edge--blocked' : ''
        }${kind === 'path' ? ' lso-learning-map__edge--path' : ''}`;
      }

      function addEdge(e, pts) {
        const sp = pts.map(point);
        if (sp.length > 1) {
          const last = sp[sp.length - 1];
          const prev = sp[sp.length - 2];
          const dx = last[0] - prev[0];
          const dy = last[1] - prev[1];
          const len = Math.sqrt((dx * dx) + (dy * dy));
          if (len > ARROW_SIZE) {
            sp[sp.length - 1] = [
              last[0] - ((dx / len) * ARROW_SIZE),
              last[1] - ((dy / len) * ARROW_SIZE),
            ];
          }
        }
        let d = `M ${Math.round(sp[0][0])} ${Math.round(sp[0][1])}`;
        for (let i = 1; i < sp.length; i += 1) {
          const same = Math.round(sp[i][0]) === Math.round(sp[i - 1][0])
                        && Math.round(sp[i][1]) === Math.round(sp[i - 1][1]);
          if (!same) {
            d += ` L ${Math.round(sp[i][0])} ${Math.round(sp[i][1])}`;
          }
        }

        const kind = edgeKind(e);
        parts.push(`<path class="${edgeClass(kind)}" d="${d}" marker-end="url(#${id}_arrow_${kind})"/>`);
        if (e.label) {
          const mid = sp[Math.floor(sp.length / 2)];
          parts.push(`<text class="lso-learning-map__edge-label" x="${mid[0] + 6}" y="${mid[1] - 8}">${
            escapeHtml(e.label)}</text>`);
        }
      }

      backwardRoutes.forEach((route, e) => {
        addEdge(e, backwardPolyline(e, route, backwardBounds));
      });

      const byEdge = new Map();
      segments.forEach((s) => {
        if (backwardRoutes.has(s.edge)) {
          return;
        }
        if (!byEdge.has(s.edge)) {
          byEdge.set(s.edge, []);
        }
        byEdge.get(s.edge).push(s);
      });

      const outSegs = {};
      const inSegs = {};
      segments.forEach((s) => {
        if (backwardRoutes.has(s.edge)) {
          return;
        }
        (outSegs[s.from.id] = outSegs[s.from.id] || []).push(s);
        (inSegs[s.to.id] = inSegs[s.to.id] || []).push(s);
      });
      Object.keys(outSegs).forEach((k) => {
        outSegs[k].sort((a, b) => a.to.x - b.to.x);
        outSegs[k].forEach((s, i) => {
          s.outIndex = i;
          s.outCount = outSegs[k].length;
        });
      });
      Object.keys(inSegs).forEach((k) => {
        inSegs[k].sort((a, b) => a.from.x - b.from.x);
        inSegs[k].forEach((s, i) => {
          s.inIndex = i;
          s.inCount = inSegs[k].length;
        });
      });

      function dockX(node, count, index) {
        if (node.dummy) {
          return node.x + DUMMY_WIDTH / 2;
        }
        // Keep the fan-out of connection points narrower for nodes with
        // many edges, so ports don't spread across the whole node width
        // (which otherwise forces bigger x-offsets and thus more/deeper
        // staircase jogs between layers).
        const span = ALONG * Math.max(0.35, 0.7 - (Math.max(0, count - 2) * 0.05));
        const left = node.x + ((ALONG - span) / 2);
        return left + ((span * (index + 1)) / (count + 1));
      }

      function exitPoint(s) {
        const node = s.from;
        if (node.dummy) {
          return [dockX(node), node.y + DEPTH];
        }
        return [dockX(node, s.outCount || 1, s.outIndex || 0), node.y + DEPTH];
      }

      function entryPoint(s) {
        const node = s.to;
        if (node.dummy) {
          return [dockX(node), node.y];
        }
        return [dockX(node, s.inCount || 1, s.inIndex || 0), node.y];
      }

      const gaps = {};
      segments.forEach((s) => {
        s.exit = exitPoint(s);
        s.entry = entryPoint(s);
        const r = s.from.rank;
        (gaps[r] = gaps[r] || []).push(s);
      });
      // Edges whose exit/entry x only differ by a small amount don't need a
      // dedicated horizontal lane in the gap between layers — routing them
      // as a (near-)straight line avoids the unnecessary "staircase" detour
      // and keeps small, harmless offsets from eating vertical space.
      const DIAGONAL_THRESHOLD = Math.max(16, M.hGap * 0.6);
      Object.keys(gaps).forEach((r) => {
        const hs = [];
        gaps[r].forEach((s) => {
          s.lane = 0;
          if (Math.abs(s.exit[0] - s.entry[0]) > DIAGONAL_THRESHOLD) {
            s.lo = Math.min(s.exit[0], s.entry[0]);
            s.hi = Math.max(s.exit[0], s.entry[0]);
            hs.push(s);
          }
        });
        const n = hs.length;
        const adj = [];
        const deg = [];
        let i;
        let j;
        for (i = 0; i < n; i += 1) {
          adj[i] = [];
          deg[i] = 0;
        }
        const inside = (s, x) => x > s.lo + 1 && x < s.hi - 1;
        const link = (a, b) => {
          adj[a].push(b);
          deg[b] += 1;
        };
        for (i = 0; i < n; i += 1) {
          for (j = 0; j < n; j += 1) {
            if (i !== j) {
              if (inside(hs[i], hs[j].entry[0])) {
                link(i, j);
              }
              if (inside(hs[i], hs[j].exit[0])) {
                link(j, i);
              }
            }
          }
        }
        const queue = [];
        let placed = 0;
        let lane = 0;
        for (i = 0; i < n; i += 1) {
          if (deg[i] === 0) {
            queue.push(i);
          }
        }
        while (queue.length) {
          const k = queue.shift();
          hs[k].lane = lane;
          lane += 1;
          placed += 1;
          adj[k].forEach((t) => {
            deg[t] -= 1;
            if (deg[t] === 0) {
              queue.push(t);
            }
          });
        }
        if (placed < n) {
          const rest = [];
          for (i = 0; i < n; i += 1) {
            if (deg[i] > 0) {
              rest.push(hs[i]);
            }
          }
          rest.sort((a, b) => (a.hi - a.lo) - (b.hi - b.lo));
          rest.forEach((s) => {
            s.lane = lane;
            lane += 1;
          });
        }
        gaps[r].forEach((s) => {
          s.laneCount = Math.max(1, lane);
        });
      });

      byEdge.forEach((segs, e) => {
        segs.sort((a, b) => a.from.rank - b.from.rank);
        const pts = [];
        segs.forEach((s) => {
          const a = s.exit;
          const b = s.entry;
          if (pts.length === 0) {
            pts.push([a[0], a[1]]);
          } else {
            pts.push([pts[pts.length - 1][0], a[1]]);
          }
          if (Math.round(a[0]) !== Math.round(b[0])) {
            const gap = b[1] - a[1];
            const n = s.laneCount || 1;
            // Cap how much of the layer gap a jog is allowed to use: with
            // the diagonal threshold above, fewer edges need a lane at all,
            // so the remaining ones can be packed into a narrower band
            // instead of spreading across the entire vGap.
            const usable = Math.min(gap - 16, Math.max(16, M.vGap * 0.5));
            const center = gap / 2;
            const spread = usable * (n > 1 ? (s.lane / (n - 1)) - 0.5 : 0);
            const my = a[1] + center + spread;
            pts.push([a[0], my]);
            pts.push([b[0], my]);
          }
          pts.push([b[0], b[1]]);
        });

        addEdge(e, pts);
      });

      svg.setAttribute('width', Math.round(width));
      svg.setAttribute('height', Math.round(height));
      svg.innerHTML = parts.join('');
    }

    let zoom = 1;
    let panX = 0;
    let panY = 0;

    // will-change: transform is only needed while the canvas is actually
    // being panned/zoomed. Keeping it on at rest forces Chrome to keep node
    // text on a composited GPU layer, which renders it slightly blurred
    // (sharpening only briefly on repaint, e.g. node hover). So it's added
    // right before a transform change and removed again shortly after the
    // last one settles.
    let settleTimer = null;

    function apply() {
      clampPan();

      const tx = Math.round(panX);
      const ty = Math.round(panY);

      canvas.classList.add('is-transforming');
      clearTimeout(settleTimer);
      settleTimer = setTimeout(() => {
        canvas.classList.remove('is-transforming');
      }, 200);

      canvas.style.transform = `translate3d(${tx}px, ${ty}px, 0) scale(${zoom})`;
      canvas.style.width = `${width}px`;
      canvas.style.height = `${height}px`;
      // Dot-grid background lives on .viewport (a static element — it never
      // moves or scales itself), so without this it stays put while the
      // canvas pans/zooms underneath it: dots and nodes visibly drift apart.
      // Re-deriving background-size/-position from panX/panY/zoom every
      // apply() call keeps the grid moving and scaling in lockstep with the
      // content, like the dots are actually part of the pannable surface.
      const dotSize = 22 * zoom;
      viewport.style.backgroundSize = `${dotSize}px ${dotSize}px`;
      viewport.style.backgroundPosition = `${panX}px ${panY}px`;
    }

    function clampZoom(z) {
      return Math.min(2.5, Math.max(0.2, z));
    }

    function contentHeight() {
      return contentBottom - contentTop;
    }

    function clampAxis(pan, start, size, view, margin) {
      const edgeStart = margin - start * zoom;
      const edgeEnd = view - margin - (start + size) * zoom;
      const lo = Math.min(edgeStart, edgeEnd);
      const hi = Math.max(edgeStart, edgeEnd);
      return Math.min(hi, Math.max(lo, pan));
    }

    function clampPan() {
      panX = clampAxis(panX, 0, width, viewport.clientWidth, PAD_Y);
      if (horizontal) {
        panY = (viewport.clientHeight - contentHeight() * zoom) / 2 - contentTop * zoom;
        return;
      }
      panY = clampAxis(panY, contentTop, contentHeight(), viewport.clientHeight, PAD_Y);
    }

    const modalWrapper = root.closest('.lso-learning-map-modal');
    const inModal = modalWrapper !== null;

    function autoHeight() {
      const wanted = Math.round(contentHeight()) + 2 * PAD_Y;
      const min = horizontal ? 0 : MIN_HEIGHT;
      viewport.style.height = `${Math.max(min, Math.min(MAX_HEIGHT, wanted))}px`;
    }

    const api = {
      zoomBy(delta) {
        if (horizontal) {
          return;
        }
        const cx = viewport.clientWidth / 2;
        const cy = viewport.clientHeight / 2;
        const nz = clampZoom(zoom + delta);
        panX = cx - (cx - panX) * (nz / zoom);
        panY = cy - (cy - panY) * (nz / zoom);
        zoom = nz;
        apply();
        announce(fmt(labels.sr_zoom, [Math.round(zoom * 100)]));
      },
      resetZoom() {
        if (horizontal) {
          return;
        }
        const ch = contentHeight();
        zoom = 1;
        panX = (viewport.clientWidth - width) / 2;
        panY = (viewport.clientHeight - ch) / 2 - contentTop;
        apply();
        announce(fmt(labels.sr_zoom, [100]));
      },
      fit(whole = true) {
        const ch = contentHeight();
        const sx = (viewport.clientWidth - 2 * PAD_Y) / width;
        const sy = (viewport.clientHeight - 2 * PAD_Y) / ch;
        if (horizontal) {
          // Never enlarge. The automatic fit (opening, resizing) only shrinks
          // to the height, so a long chain stays readable and is panned
          // sideways; "fit view" shrinks until width and height fit.
          // clampPan() centres panY with the current zoom, so only panX is
          // set here.
          zoom = clampZoom(whole ? Math.min(1, sx, sy) : Math.min(1, sy));
          // centred if the chain fits, otherwise it starts at the left edge
          panX = Math.max(0, (viewport.clientWidth - width * zoom) / 2);
          apply();
          announce(labels.sr_fitted);
          return;
        }
        zoom = clampZoom(Math.min(sx, sy));
        panX = (viewport.clientWidth - width * zoom) / 2;
        panY = (viewport.clientHeight - ch * zoom) / 2 - contentTop * zoom;
        apply();
        announce(labels.sr_fitted);
      },
      focusCurrent() {
        if (horizontal) {
          return;
        }
        let cur = null;
        order.forEach((k) => {
          if (nodes[k].raw.current) {
            cur = nodes[k];
          }
        });
        if (!cur) {
          announce(labels.sr_no_current);
          return;
        }
        zoom = clampZoom(1);
        panX = viewport.clientWidth / 2 - (cur.sx + M.nodeWidth / 2) * zoom;
        panY = viewport.clientHeight / 2 - (cur.sy + M.nodeHeight / 2) * zoom;
        apply();
        announce(fmt(labels.sr_at_current, [cur.raw.title]));
      },
    };

    viewport.addEventListener('wheel', (e) => {
      let dx = 0;
      if (Math.abs(e.deltaX) >= 1) {
        dx = e.deltaX;
      } else if (e.shiftKey) {
        dx = e.deltaY;
      }
      if (dx !== 0 && !e.ctrlKey) {
        e.preventDefault();
        panX -= dx;
        apply();
        return;
      }
      if (horizontal) {
        return;
      }
      if (!e.ctrlKey && Math.abs(e.deltaY) < 1) {
        return;
      }
      e.preventDefault();
      const rect = viewport.getBoundingClientRect();
      const mx = e.clientX - rect.left;
      const my = e.clientY - rect.top;
      const nz = clampZoom(zoom * (e.deltaY < 0 ? 1.1 : 0.9));
      panX = mx - (mx - panX) * (nz / zoom);
      panY = my - (my - panY) * (nz / zoom);
      zoom = nz;
      apply();
    }, { passive: false });

    let drag = null;
    viewport.addEventListener('pointerdown', (e) => {
      if (e.target.closest('a')) {
        return;
      }
      drag = {
        x: e.clientX, y: e.clientY, px: panX, py: panY,
      };
      viewport.classList.add('is-panning');
      viewport.setPointerCapture(e.pointerId);
    });
    viewport.addEventListener('pointermove', (e) => {
      if (!drag) {
        return;
      }
      panX = drag.px + (e.clientX - drag.x);
      panY = drag.py + (e.clientY - drag.y);
      apply();
    });
    ['pointerup', 'pointercancel'].forEach((ev) => {
      viewport.addEventListener(ev, () => {
        drag = null;
        viewport.classList.remove('is-panning');
      });
    });
    viewport.addEventListener('keydown', (e) => {
      const step = 40;
      if (e.key === 'ArrowLeft') {
        panX += step;
      } else if (e.key === 'ArrowRight') {
        panX -= step;
      } else if (e.key === 'ArrowUp') {
        if (horizontal) {
          return;
        }
        panY += step;
      } else if (e.key === 'ArrowDown') {
        if (horizontal) {
          return;
        }
        panY -= step;
      } else if (e.key === '+') {
        if (horizontal) {
          return;
        }
        api.zoomBy(0.15);
        return;
      } else if (e.key === '-') {
        if (horizontal) {
          return;
        }
        api.zoomBy(-0.15);
        return;
      } else {
        return;
      }
      e.preventDefault();
      apply();
    });

    rank();
    build();
    ordering();
    positions();
    toScreen();
    drawNodes();
    drawEdges();
    autoHeight();
    apply();
    silently(() => api.fit(false));
    announcementsOn = true;

    if (window.ResizeObserver) {
      let lastW = viewport.clientWidth;
      let lastH = viewport.clientHeight;
      const observer = new window.ResizeObserver(() => {
        const w = viewport.clientWidth;
        const h = viewport.clientHeight;
        if (w === 0 || (w === lastW && h === lastH)) {
          return;
        }
        const wasHidden = lastW === 0;
        lastW = w;
        lastH = h;
        if (wasHidden) {
          autoHeight();
          silently(() => api.fit(false));
          return;
        }
        if (inModal) {
          silently(() => api.fit(false));
          return;
        }
        apply();
      });
      observer.observe(viewport);
    }

    if (inModal) {
      window.addEventListener('resize', () => {
        silently(() => api.fit(false));
      });
    }

    return api;
  }

  function init(root) {
    if (!root || root.dataset.lsoLearningMapInitialised === 'true') {
      return;
    }
    const source = root.querySelector('[data-lso-learning-map-data]');
    if (!source) {
      return;
    }

    let data;
    try {
      data = JSON.parse(source.textContent);
    } catch (e) {
      return;
    }

    const api = create(root, data);
    if (!api) {
      return;
    }
    root.dataset.lsoLearningMapInitialised = 'true';
    instances[root.id] = api;
  }

  function initAll(context) {
    (context || document).querySelectorAll(CONTAINER_SELECTOR).forEach(init);
  }

  window.il = window.il || {};
  window.il.LSO = window.il.LSO || {};
  window.il.LSO.LearningMap = {
    init,
    initAll,
    get(mapId) {
      return instances[mapId] || null;
    },
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initAll();
    });
  } else {
    initAll();
  }
}(window, document));

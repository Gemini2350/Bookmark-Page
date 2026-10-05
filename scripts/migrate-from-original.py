#!/usr/bin/env python3
"""Migrate all groups and bookmarks from an original Bookmark-Page
(LeeO86/Bookmark-Page, no export feature) into this fork.

Usage:
  # write an import file (use it with the Import / Export tab):
  ./migrate-from-original.py http://old-host:8080 > export.json

  # or migrate directly into a new instance:
  ./migrate-from-original.py http://old-host:8080 http://new-host:8080

Only the Python standard library is used.
"""
import json
import sys
import urllib.parse
import urllib.request


def fetch_old(base):
    data = urllib.parse.urlencode({'sort': 'sort', 'asc': 'true'}).encode()
    req = urllib.request.Request(base.rstrip('/') + '/php/getBookmarks.php', data=data)
    with urllib.request.urlopen(req, timeout=15) as r:
        return json.load(r)


def to_export(old):
    groups = []
    groupdata = old.get('groupdata', {})
    for gname, bms in old.get('bookmarks', {}).items():
        g = groupdata.get(gname, {})
        bookmarks = []
        for i, b in enumerate(bms):
            bm = {
                'name': b.get('name', ''),
                'link': b.get('link', ''),
                'favicon': b.get('favicon', ''),
                'remarks': b.get('remarks', ''),
                'sort': int(b.get('sort') or i + 1),
            }
            for j in range(1, 9):
                bm['user%d' % j] = b.get('user%d' % j, '')
            bookmarks.append(bm)
        groups.append({
            'name': gname,
            'remarks': g.get('remarks', ''),
            'sort': int(g.get('sort') or len(groups) + 1),
            'bookmarks': bookmarks,
        })
    return {'type': 'bookmark-page-export', 'version': '1.3.0', 'groups': groups}


def post_new(base, export):
    data = urllib.parse.urlencode({'json': json.dumps(export)}).encode()
    req = urllib.request.Request(base.rstrip('/') + '/php/importBookmarks.php', data=data)
    with urllib.request.urlopen(req, timeout=30) as r:
        return json.load(r)


if __name__ == '__main__':
    if len(sys.argv) < 2:
        sys.exit('usage: migrate-from-original.py <old-url> [<new-url>]')
    export = to_export(fetch_old(sys.argv[1]))
    total = sum(len(g['bookmarks']) for g in export['groups'])
    print('Found %d bookmarks in %d groups on %s'
          % (total, len(export['groups']), sys.argv[1]), file=sys.stderr)
    if len(sys.argv) > 2:
        result = post_new(sys.argv[2], export)
        print('Import result from %s: %s' % (sys.argv[2], result), file=sys.stderr)
    else:
        json.dump(export, sys.stdout, indent=2, ensure_ascii=False)
        sys.stdout.write('\n')

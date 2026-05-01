# WebDAV Manual / E2E Test Suite

End-to-end checks against a running ILIAS WebDAV endpoint. Simulates real
clients (Windows Explorer / macOS Finder) by reproducing the exact request
sequences and headers they send.

Reference: https://sabre.io/dav/clients/finder/ and
https://sabre.io/dav/clients/windows/

## Configuration

```bash
export DAV_BASE="http://11.ilias.localhost/webdav.php"
export DAV_CLIENT="default"           # client_id segment (optional in URL)
export DAV_USER="root"
export DAV_PASS="didi"
export DAV_REF="ref_92"               # an existing writable folder (cat/grp/fold/crs)

# wrapper — works in bash and zsh
dav() { curl -s -u "$DAV_USER:$DAV_PASS" "$@"; }
```

Use `$DAV_BASE/$DAV_REF` for new-style URLs and `$DAV_BASE/$DAV_CLIENT/$DAV_REF`
for legacy URLs. Both must behave identically.

## Quick smoke test (both URL forms)

```bash
# new-style URL
dav -o /dev/null -w "new:    %{http_code}\n" \
     -X PROPFIND -H "Depth: 0" "$DAV_BASE/$DAV_REF"

# legacy URL with client_id
dav -o /dev/null -w "legacy: %{http_code}\n" \
     -X PROPFIND -H "Depth: 0" "$DAV_BASE/$DAV_CLIENT/$DAV_REF"
```

Expected: both `207`.

---

## Scenario 1 — Windows Explorer (`Microsoft-WebDAV-MiniRedir`)

Windows behaviour:
- Sends `OPTIONS /` first to detect WebDAV.
- Then `PROPFIND` Depth 0 to validate the URL.
- Sends `Translate: f` on GET / PUT.
- `User-Agent: Microsoft-WebDAV-MiniRedir/10.0.19041`.
- LOCK before edit; UNLOCK afterwards.
- Creates `desktop.ini` and `Thumbs.db` (we do not need to recreate, just
  verify they don't blow up if present).

```bash
UA_WIN="Microsoft-WebDAV-MiniRedir/10.0.19041"
TARGET="$DAV_BASE/$DAV_REF"

# 1. capability discovery
dav -o /dev/null -w "OPTIONS:        %{http_code}\n" \
     -A "$UA_WIN" -X OPTIONS "$TARGET"

# 2. discover root
dav -o /dev/null -w "PROPFIND root:  %{http_code}\n" \
     -A "$UA_WIN" -X PROPFIND -H "Depth: 0" -H "Translate: f" "$TARGET"

# 3. list children
dav -o /dev/null -w "PROPFIND list:  %{http_code}\n" \
     -A "$UA_WIN" -X PROPFIND -H "Depth: 1" -H "Translate: f" "$TARGET" \
     --data-binary '<?xml version="1.0" encoding="utf-8"?>
<D:propfind xmlns:D="DAV:">
  <D:prop>
    <D:resourcetype/>
    <D:getcontentlength/>
    <D:getlastmodified/>
    <D:creationdate/>
    <D:displayname/>
  </D:prop>
</D:propfind>' \
     -H "Content-Type: text/xml; charset=utf-8"

# 4. create folder
dav -o /dev/null -w "MKCOL:          %{http_code}\n" \
     -A "$UA_WIN" -X MKCOL "$TARGET/win_test_folder"

# 5. lock file (Windows takes a write lock before PUT)
LOCK_RESP=$(dav -A "$UA_WIN" -X LOCK \
     -H "Content-Type: text/xml; charset=utf-8" \
     -H "Timeout: Second-3600" \
     -H "Depth: 0" \
     --data-binary '<?xml version="1.0" encoding="utf-8"?>
<D:lockinfo xmlns:D="DAV:">
  <D:lockscope><D:exclusive/></D:lockscope>
  <D:locktype><D:write/></D:locktype>
  <D:owner><D:href>windows-explorer</D:href></D:owner>
</D:lockinfo>' \
     "$TARGET/win_test.txt")
LOCK_TOKEN=$(echo "$LOCK_RESP" | grep -oE 'opaquelocktoken:[^<]+' | head -1)
echo "LOCK token:     $LOCK_TOKEN"

# 6. PUT (with lock token in If header — Windows always does this)
dav -o /dev/null -w "PUT (locked):   %{http_code}\n" \
     -A "$UA_WIN" -X PUT \
     -H "If: (<$LOCK_TOKEN>)" \
     -H "Translate: f" \
     -H "Content-Type: application/octet-stream" \
     --data-binary "hello from windows" \
     "$TARGET/win_test.txt"

# 7. GET back
dav -o /tmp/win_get.txt -w "GET:            %{http_code}\n" \
     -A "$UA_WIN" -H "Translate: f" "$TARGET/win_test.txt"
echo "GET body:       $(cat /tmp/win_get.txt)"

# 8. UNLOCK
dav -o /dev/null -w "UNLOCK:         %{http_code}\n" \
     -A "$UA_WIN" -X UNLOCK \
     -H "Lock-Token: <$LOCK_TOKEN>" \
     "$TARGET/win_test.txt"

# 9. PROPPATCH (Windows updates Win32 file attributes)
dav -o /dev/null -w "PROPPATCH:      %{http_code}\n" \
     -A "$UA_WIN" -X PROPPATCH \
     -H "Content-Type: text/xml; charset=utf-8" \
     --data-binary '<?xml version="1.0" encoding="utf-8" ?>
<D:propertyupdate xmlns:D="DAV:" xmlns:Z="urn:schemas-microsoft-com:">
  <D:set>
    <D:prop>
      <Z:Win32LastModifiedTime>Fri, 01 May 2026 12:00:00 GMT</Z:Win32LastModifiedTime>
    </D:prop>
  </D:set>
</D:propertyupdate>' \
     "$TARGET/win_test.txt"

# 10. COPY
dav -o /dev/null -w "COPY:           %{http_code}\n" \
     -A "$UA_WIN" -X COPY \
     -H "Destination: $TARGET/win_test_copy.txt" \
     -H "Overwrite: T" \
     "$TARGET/win_test.txt"

# 11. MOVE / rename
dav -o /dev/null -w "MOVE:           %{http_code}\n" \
     -A "$UA_WIN" -X MOVE \
     -H "Destination: $TARGET/win_test_renamed.txt" \
     -H "Overwrite: T" \
     "$TARGET/win_test.txt"

# 12. DELETE everything created
dav -o /dev/null -w "DELETE renamed: %{http_code}\n" \
     -A "$UA_WIN" -X DELETE "$TARGET/win_test_renamed.txt"
dav -o /dev/null -w "DELETE copy:    %{http_code}\n" \
     -A "$UA_WIN" -X DELETE "$TARGET/win_test_copy.txt"
dav -o /dev/null -w "DELETE folder:  %{http_code}\n" \
     -A "$UA_WIN" -X DELETE "$TARGET/win_test_folder"
```

Expected (rough):
- OPTIONS / PROPFIND / GET → `200` / `207` / `200`
- MKCOL → `201`
- LOCK → `200`
- PUT (initial) → `201` (created) — re-PUT → `204`
- UNLOCK → `204`
- PROPPATCH → `207`
- COPY / MOVE → `201` or `204`
- DELETE → `204`

---

## Scenario 2 — macOS Finder (`WebDAVFS/Darwin`)

Finder behaviour (from sabre.io/dav/clients/finder):
- `User-Agent: WebDAVFS/3.0.0 (03008000) Darwin/22.6.0 (x86_64)`.
- Sends `OPTIONS /` to detect.
- `PROPFIND` Depth 1 with `<allprop/>`.
- LOCK before PUT (Finder uses LOCK aggressively).
- PUT in two phases: zero-byte first (creates "stub"), then real content.
- Sends `X-Expected-Entity-Length` (legacy chunked-PUT compatibility).
- Creates AppleDouble companion files `._<filename>` and `.DS_Store`.
- Tries to `MKCOL .TemporaryItems` and `.Trashes` near root.

```bash
UA_MAC="WebDAVFS/3.0.0 (03008000) Darwin/22.6.0 (x86_64)"
TARGET="$DAV_BASE/$DAV_REF"

# 1. discover
dav -o /dev/null -w "OPTIONS:           %{http_code}\n" \
     -A "$UA_MAC" -X OPTIONS "$TARGET"

# 2. allprop list
dav -o /dev/null -w "PROPFIND allprop:  %{http_code}\n" \
     -A "$UA_MAC" -X PROPFIND -H "Depth: 1" \
     -H "Content-Type: text/xml; charset=utf-8" \
     --data-binary '<?xml version="1.0" encoding="utf-8"?>
<D:propfind xmlns:D="DAV:"><D:allprop/></D:propfind>' \
     "$TARGET"

# 3. Finder probes for hidden meta dirs (these may legitimately fail)
dav -o /dev/null -w "MKCOL .TempItems:  %{http_code}\n" \
     -A "$UA_MAC" -X MKCOL "$TARGET/.TemporaryItems"
dav -o /dev/null -w "MKCOL .Trashes:    %{http_code}\n" \
     -A "$UA_MAC" -X MKCOL "$TARGET/.Trashes"

# 4. create folder
dav -o /dev/null -w "MKCOL:             %{http_code}\n" \
     -A "$UA_MAC" -X MKCOL "$TARGET/mac_test_folder"

# 5. zero-byte PUT to create the file *before* LOCK.
#    Our backend resolves locks via the resource's obj_id, so the target
#    must exist before LOCK is taken. Finder usually does this implicitly
#    via a stub PUT; we replicate it here.
dav -o /dev/null -w "PUT stub:          %{http_code}\n" \
     -A "$UA_MAC" -X PUT \
     -H "X-Expected-Entity-Length: 0" \
     -H "Content-Length: 0" \
     "$TARGET/mac_test.txt"

# 6. LOCK target file (Finder always locks before further writes)
LOCK_RESP=$(dav -A "$UA_MAC" -X LOCK \
     -H "Content-Type: text/xml; charset=utf-8" \
     -H "Timeout: Second-600" \
     -H "Depth: 0" \
     --data-binary '<?xml version="1.0" encoding="utf-8"?>
<D:lockinfo xmlns:D="DAV:">
  <D:lockscope><D:exclusive/></D:lockscope>
  <D:locktype><D:write/></D:locktype>
  <D:owner>finder@mac</D:owner>
</D:lockinfo>' \
     "$TARGET/mac_test.txt")
LOCK_TOKEN=$(echo "$LOCK_RESP" | grep -oE 'opaquelocktoken:[^<]+' | head -1)
echo "LOCK token:        $LOCK_TOKEN"

# 7. real PUT (Finder phase 2 — uses chunked + X-Expected-Entity-Length)
BODY="hello from finder"
dav -o /dev/null -w "PUT phase 2:       %{http_code}\n" \
     -A "$UA_MAC" -X PUT \
     -H "If: (<$LOCK_TOKEN>)" \
     -H "X-Expected-Entity-Length: ${#BODY}" \
     --data-binary "$BODY" \
     "$TARGET/mac_test.txt"

# 8. AppleDouble metadata file
APPLEDOUBLE_HEX="0005160700020000"   # AppleDouble magic bytes (truncated)
dav -o /dev/null -w "PUT ._mac_test:    %{http_code}\n" \
     -A "$UA_MAC" -X PUT \
     -H "X-Expected-Entity-Length: 8" \
     --data-binary "$APPLEDOUBLE_HEX" \
     "$TARGET/._mac_test.txt"

# 9. .DS_Store
dav -o /dev/null -w "PUT .DS_Store:     %{http_code}\n" \
     -A "$UA_MAC" -X PUT \
     -H "X-Expected-Entity-Length: 4" \
     --data-binary "DSDB" \
     "$TARGET/.DS_Store"

# 10. GET back
dav -o /tmp/mac_get.txt -w "GET:               %{http_code}\n" \
     -A "$UA_MAC" "$TARGET/mac_test.txt"
echo "GET body:          $(cat /tmp/mac_get.txt)"

# 11. UNLOCK
dav -o /dev/null -w "UNLOCK:            %{http_code}\n" \
     -A "$UA_MAC" -X UNLOCK \
     -H "Lock-Token: <$LOCK_TOKEN>" \
     "$TARGET/mac_test.txt"

# 12. MOVE / rename (Finder rename)
dav -o /dev/null -w "MOVE:              %{http_code}\n" \
     -A "$UA_MAC" -X MOVE \
     -H "Destination: $TARGET/mac_test_renamed.txt" \
     -H "Overwrite: T" \
     "$TARGET/mac_test.txt"

# 13. cleanup
dav -o /dev/null -w "DELETE renamed:    %{http_code}\n" \
     -A "$UA_MAC" -X DELETE "$TARGET/mac_test_renamed.txt"
dav -o /dev/null -w "DELETE ._:         %{http_code}\n" \
     -A "$UA_MAC" -X DELETE "$TARGET/._mac_test.txt"
dav -o /dev/null -w "DELETE .DS_Store:  %{http_code}\n" \
     -A "$UA_MAC" -X DELETE "$TARGET/.DS_Store"
dav -o /dev/null -w "DELETE folder:     %{http_code}\n" \
     -A "$UA_MAC" -X DELETE "$TARGET/mac_test_folder"
```

Expected:
- `MKCOL .TemporaryItems` / `.Trashes` may legitimately return `403` if invalid
  start chars are blocked (see `Config::getInvalidStartCharacters`) — that's
  fine, log only.
- All LOCK/PUT/GET/MOVE/DELETE → `200` / `201` / `204` / `207`.

---

## Authentication failure tests

```bash
# wrong password — must be 401
dav -o /dev/null -w "wrong pw:       %{http_code}\n" \
     -u "$DAV_USER:WRONG" -X PROPFIND -H "Depth: 0" "$DAV_BASE/$DAV_REF"

# anonymous — must be 401
curl -s -o /dev/null -w "anonymous:      %{http_code}\n" \
     -X PROPFIND -H "Depth: 0" "$DAV_BASE/$DAV_REF"

# valid creds, ref that does not exist — must be 404
dav -o /dev/null -w "missing ref:    %{http_code}\n" \
     -X PROPFIND -H "Depth: 0" "$DAV_BASE/ref_99999999"
```

---

## Range / partial GET (resume / preview clients)

```bash
dav -o /dev/null -w "range 0-9:      %{http_code} (expect 206)\n" \
     -H "Range: bytes=0-9" -X GET "$TARGET/some_existing_file"
```

---

## Notes on URL-form parity

Every block above can be re-run by replacing `$TARGET` with the legacy form:

```bash
TARGET="$DAV_BASE/$DAV_CLIENT/$DAV_REF"
```

Status codes and bodies must match the new-style form.

---

## Known limitations

**LOCK on a non-existent target fails.**
Our backend keys locks on the resource's ILIAS `obj_id`, so the target must
already exist when LOCK is issued. Finder normally PUTs a zero-byte stub
before locking — the Finder scenario above does this explicitly.

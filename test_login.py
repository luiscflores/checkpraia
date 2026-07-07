import requests
import json
import re

session = requests.Session()
session.headers.update({'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0'})

# Get login page
r = session.get('https://www.checkpraia.pt/login', verify=True)
print('GET /login:', r.status_code)
print('Cookies:', dict(session.cookies))

# Extract CSRF token
match = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
token = match.group(1)
print('CSRF token:', token[:20] + '...')

# Extract Livewire snapshot
match = re.search(r'wire:snapshot="([^"]+)"', r.text)
raw_snapshot = match.group(1).replace('&quot;', '"')
print('Raw snapshot form data:', raw_snapshot[:120] + '...')

# Parse snapshot to update email/password
decoded = requests.utils.quote(raw_snapshot, safe='/:{}[]",')
print('Decoded would be too long')
# Actually let me use json module directly
import html
decoded = html.unescape(raw_snapshot)
parsed = json.loads(decoded)
print('Parsed data keys:', list(parsed.get('data', {}).keys()))

# Update the email and password in the form
form = parsed['data']['form']
if isinstance(form, list):
    # Form is [field_values, metadata]
    form[0]['email'] = 'admin@checkpraia.pt'
    form[0]['password'] = 'password'
parsed['data']['form'] = form

# Re-serialize
new_snapshot = json.dumps(parsed)
print('Updated snapshot data:', new_snapshot[:100] + '...')

# Prepare Livewire update request
lw_data = {
    'components': [{
        'snapshot': new_snapshot,
        'calls': [{'path': '', 'method': 'login', 'params': []}]
    }]
}

# Use X-XSRF-TOKEN from cookie
xsrf_token = requests.utils.unquote(session.cookies.get('XSRF-TOKEN', ''))
print('XSRF-TOKEN cookie:', xsrf_token[:20] if xsrf_token else 'NONE')

headers = {
    'Accept': 'application/json',
    'Referer': 'https://www.checkpraia.pt/login',
    'X-Requested-With': 'XMLHttpRequest',
}

# Try with XSRF-TOKEN cookie
if xsrf_token:
    headers['X-XSRF-TOKEN'] = xsrf_token
else:
    headers['X-CSRF-TOKEN'] = token

r = session.post(
    'https://www.checkpraia.pt/livewire/update',
    json=lw_data,
    headers=headers,
)
print('POST /livewire/update:', r.status_code)
print('Content-Type:', r.headers.get('Content-Type', 'N/A'))
body = r.text
if r.status_code == 500:
    print('500 ERROR BODY:')
    print(body[:2000])
elif r.status_code == 200:
    print('SUCCESS:', body[:500])
else:
    print('Response:', body[:500])

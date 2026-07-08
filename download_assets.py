import os
import urllib.request
import urllib.parse
import re

base = os.path.abspath(os.path.dirname(__file__))
leaflet_dir = os.path.join(base, 'public', 'lib', 'leaflet')
chart_dir = os.path.join(base, 'public', 'lib', 'chartjs')
fonts_dir = os.path.join(base, 'public', 'fonts')

os.makedirs(os.path.join(leaflet_dir, 'images'), exist_ok=True)
os.makedirs(chart_dir, exist_ok=True)
os.makedirs(fonts_dir, exist_ok=True)

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}

files = [
    ('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', os.path.join(leaflet_dir, 'leaflet.css')),
    ('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', os.path.join(leaflet_dir, 'leaflet.js')),
    ('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', os.path.join(chart_dir, 'chart.umd.min.js')),
    ('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap', os.path.join(fonts_dir, 'fonts-sora-inter.css')),
    ('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap', os.path.join(fonts_dir, 'fonts-inter-dm.css')),
]

leaflet_image_urls = [
    'https://unpkg.com/leaflet@1.9.4/dist/images/layers.png',
    'https://unpkg.com/leaflet@1.9.4/dist/images/layers-2x.png',
    'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
]
for image_url in leaflet_image_urls:
    filename = os.path.basename(image_url)
    target_path = os.path.join(leaflet_dir, 'images', filename)
    try:
        req = urllib.request.Request(image_url, headers=headers)
        with urllib.request.urlopen(req, timeout=20) as resp:
            data = resp.read()
        with open(target_path, 'wb') as f:
            f.write(data)
        print('SAVED', target_path, len(data))
    except Exception as e:
        print('ERROR', image_url, type(e).__name__, e)

for url, path in files:
    try:
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=20) as resp:
            data = resp.read()
        with open(path, 'wb') as f:
            f.write(data)
        print('SAVED', path, len(data))
    except Exception as e:
        print('ERROR', url, type(e).__name__, e)

# Download Google font files referenced in the CSS files
font_css_paths = [os.path.join(fonts_dir, 'fonts-sora-inter.css'), os.path.join(fonts_dir, 'fonts-inter-dm.css')]

for css_path in font_css_paths:
    if not os.path.exists(css_path):
        continue
    with open(css_path, 'r', encoding='utf-8') as f:
        css_text = f.read()
    urls = re.findall(r'url\((https://[^)]+)\)', css_text)
    for font_url in set(urls):
        parsed = urllib.parse.urlparse(font_url)
        filename = os.path.basename(parsed.path)
        if not filename:
            continue
        font_path = os.path.join(fonts_dir, filename)
        if os.path.exists(font_path):
            print('ALREADY', font_path)
            continue
        try:
            req = urllib.request.Request(font_url, headers=headers)
            with urllib.request.urlopen(req, timeout=20) as resp:
                data = resp.read()
            with open(font_path, 'wb') as f:
                f.write(data)
            print('SAVED FONT', font_path, len(data))
        except Exception as e:
            print('ERROR FONT', font_url, type(e).__name__, e)

print('DONE')

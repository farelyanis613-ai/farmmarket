import os
import urllib.request

url = "https://unpkg.com/tailwindcss@3.5.0/dist/tailwind.min.css"
output_path = os.path.join("public", "lib", "tailwind", "tailwind.min.css")
os.makedirs(os.path.dirname(output_path), exist_ok=True)
print(f"Downloading {url} to {output_path}...")
urllib.request.urlretrieve(url, output_path)
print("Download complete.")

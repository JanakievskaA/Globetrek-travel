#!/usr/bin/env python3
"""Fetch the curated travel photo library used by the GlobeTrek seeders.

Every photo below was visually verified to match its slug. Each source image is
downloaded once and written out at two widths:

    public/assets/images/travel/<slug>.jpg        1600px  hero / gallery / lightbox
    public/assets/images/travel/<slug>-card.jpg    800px  cards, thumbnails, admin tables

Run from the application root:  python3 tools/fetch_photos.py [--force]

Photos are served by Unsplash under the Unsplash License (free for commercial
and non-commercial use, no attribution required).
"""
from __future__ import annotations

import sys
import urllib.request
from concurrent.futures import ThreadPoolExecutor
from io import BytesIO
from pathlib import Path

from PIL import Image

OUT_DIR = Path(__file__).resolve().parent.parent / "public/assets/images/travel"
FULL_WIDTH = 1600
CARD_WIDTH = 800
UA = {"User-Agent": "GlobeTrek-Seeder/1.0"}

# slug -> Unsplash photo id
CATALOGUE: dict[str, str] = {
    # --- Indonesia -------------------------------------------------------
    "bali-ulun-danu": "1537996194471-e657df975ab4",
    "bali-kelingking": "1539367628448-4bc5c9d171c8",
    # --- Greece ----------------------------------------------------------
    "santorini-oia": "1533105079780-92b9be482077",
    "santorini-village": "1533104816931-20fa691ff6ca",
    # --- Japan -----------------------------------------------------------
    "kyoto-sakura": "1524413840807-0c3cb6fa808d",
    "kyoto-street": "1480796927426-f609979314bd",
    "tokyo-alley": "1533050487297-09b450131914",
    # --- France ----------------------------------------------------------
    "paris-eiffel": "1502602898657-3e91760cbb34",
    "paris-bridge": "1499856871958-5b9627545d1a",
    # --- Italy -----------------------------------------------------------
    "rome-trevi": "1525874684015-58379d421a52",
    "rome-vatican": "1531572753322-ad063cecc140",
    "venice-rialto": "1523906834658-6e24ef2386f9",
    "venice-canal": "1498307833015-e7b400441eb8",
    "cinque-terre": "1516483638261-f4dbaf036963",
    "dolomites-braies": "1476514525535-07fb3b4ae5f1",
    "dolomites-boat": "1501785888041-af3ef285b470",
    # --- UAE -------------------------------------------------------------
    "dubai-skyline": "1512453979798-5ea266f8880c",
    "dubai-burj-al-arab": "1518684079-3c830dcef090",
    # --- Peru ------------------------------------------------------------
    "machu-picchu": "1526392060635-9d6019884377",
    # --- Maldives --------------------------------------------------------
    "maldives-seaplane": "1512100356356-de1b84283e18",
    # --- South Africa ----------------------------------------------------
    "cape-town-aerial": "1580060839134-75a5edca2e99",
    "safari-sunset": "1516426122078-c23e76319801",
    # --- Vietnam ---------------------------------------------------------
    "halong-bay": "1528127269322-539801943592",
    # --- Canada ----------------------------------------------------------
    "banff-moraine": "1493246507139-91e8fad9978e",
    # --- United Kingdom --------------------------------------------------
    "london-tower-bridge": "1533929736458-ca588d08c8be",
    "london-big-ben": "1486299267070-83823f5448dd",
    # --- United States ---------------------------------------------------
    "nyc-brooklyn-bridge": "1518391846015-55a9cc003b25",
    "big-sur-coast": "1510414842594-a61c69b5ae57",
    "desert-road-trip": "1469854523086-cc02fe5d8800",
    # --- Alps / Himalaya -------------------------------------------------
    "alps-hiker": "1526772662000-3f88f10405ff",
    "himalaya-peaks": "1544735716-392fe2489ffa",
    "alpine-forest": "1464822759023-fed622ff2c3b",
    "mountains-clouds": "1506905925346-21bda4d32df4",
    # --- Thailand --------------------------------------------------------
    "thailand-longtail": "1552465011-b4e21bf6e79a",
    "thailand-resort": "1520250497591-112f2f40a3f4",
    "thailand-karst-lake": "1504457047772-27faf1c00561",
    "chiang-mai-temple": "1528181304800-259b08848526",
    # --- India -----------------------------------------------------------
    "taj-mahal": "1548013146-72479768bada",
    # --- Turkey ----------------------------------------------------------
    "cappadocia-balloons": "1530789253388-582c481c54b0",
    # --- Brazil ----------------------------------------------------------
    "rio-sugarloaf": "1483729558449-99ef09a8c325",
    # --- Portugal --------------------------------------------------------
    "algarve-beach": "1493558103817-58b2924bce98",
    # --- Atmosphere / activity -------------------------------------------
    "beach-sunset": "1507525428034-b723cf961d3e",
    "beach-palm": "1519046904884-53103b34b206",
    "palms-sky": "1454391304352-2bf4678b1a7a",
    "forest-boardwalk": "1447752875215-b2761acb3c5d",
    "forest-light": "1441974231531-c6227db76b6e",
    "milky-way-mountains": "1519681393784-d120267933ba",
    "camping-tent-stars": "1517824806704-9040b037703b",
    "highlands-mist": "1470071459604-3b5ec3a7fe05",
    "traveler-silhouette": "1473625247510-8ceb1760943f",
    "airplane-clouds": "1500835556837-99ac94a94552",
    "hiking-compass": "1484910292437-025e5d13ce87",
}


def source_url(photo_id: str) -> str:
    return (f"https://images.unsplash.com/photo-{photo_id}"
            f"?w={FULL_WIDTH}&q=80&fm=jpg&fit=max")


def save_variant(image: Image.Image, path: Path, width: int) -> None:
    ratio = width / image.width
    resized = image if ratio >= 1 else image.resize(
        (width, max(1, round(image.height * ratio))), Image.LANCZOS)
    resized.save(path, "JPEG", quality=82, optimize=True, progressive=True)


def fetch(item: tuple[str, str], force: bool) -> str:
    slug, photo_id = item
    full, card = OUT_DIR / f"{slug}.jpg", OUT_DIR / f"{slug}-card.jpg"
    if not force and full.exists() and card.exists():
        return f"  skip   {slug}"
    try:
        req = urllib.request.Request(source_url(photo_id), headers=UA)
        raw = urllib.request.urlopen(req, timeout=60).read()
        image = Image.open(BytesIO(raw)).convert("RGB")
        save_variant(image, full, FULL_WIDTH)
        save_variant(image, card, CARD_WIDTH)
    except Exception as exc:                                     # noqa: BLE001
        return f"  FAIL   {slug}: {exc}"
    return f"  ok     {slug} ({image.width}x{image.height})"


def main() -> int:
    force = "--force" in sys.argv
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    print(f"Fetching {len(CATALOGUE)} photos into {OUT_DIR}")
    with ThreadPoolExecutor(8) as pool:
        results = list(pool.map(lambda i: fetch(i, force), CATALOGUE.items()))
    for line in results:
        print(line)
    failures = [r for r in results if "FAIL" in r]
    print(f"\n{len(CATALOGUE) - len(failures)} ok, {len(failures)} failed")
    return 1 if failures else 0


if __name__ == "__main__":
    raise SystemExit(main())

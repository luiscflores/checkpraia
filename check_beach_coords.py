#!/usr/bin/env python3
"""
Validate beach coordinates using BigDataCloud reverse geocoding API (free, no key needed).
Checks if each beach point is on land/beach or in the sea.
"""

import urllib.request
import urllib.parse
import json
import time

BEACHES = [
    (103, "Praia Formosa (Santa Maria)", "Açores", 36.9489, -25.0956),
    (102, "Praia de Porto Pim", "Açores", 38.5256, -28.6256),
    (100, "Praia de Santa Bárbara", "Açores", 37.8189, -25.5344),
    (101, "Praia do Fogo (Ribeira Quente)", "Açores", 37.7389, -25.3056),
    (59,  "Meia Praia", "Continental", 37.1139, -8.6556),
    (33,  "Praia Grande", "Continental", 38.8156, -9.4803),
    (74,  "Praia da Adraga", "Continental", 38.8028, -9.4856),
    (11,  "Praia da Aguda", "Continental", 41.0456, -8.6528),
    (53,  "Praia da Arrifana", "Continental", 37.2922, -8.8656),
    (16,  "Praia da Barra", "Continental", 40.644167, -8.748333),
    (13,  "Praia da Baía", "Continental", 40.9922, -8.6489),
    (81,  "Praia da Califórnia", "Continental", 38.4419, -9.1006),
    (22,  "Praia da Claridade", "Continental", 40.1506, -8.8686),
    (45,  "Praia da Comporta", "Continental", 38.3806, -8.8022),
    (17,  "Praia da Costa Nova", "Continental", 40.6139, -8.7514),
    (57,  "Praia da Dona Ana", "Continental", 37.0914, -8.6694),
    (37,  "Praia da Duquesa", "Continental", 38.6989, -9.4156),
    (62,  "Praia da Falésia", "Continental", 37.0858, -8.1469),
    (41,  "Praia da Figueirinha", "Continental", 38.4839, -8.9406),
    (40,  "Praia da Fonte da Telha", "Continental", 38.5678, -9.1931),
    (28,  "Praia da Foz do Arelho", "Continental", 39.4322, -9.2256),
    (92,  "Praia da Galé", "Continental", 37.0828, -8.3189),
    (12,  "Praia da Granja", "Continental", 41.0256, -8.6503),
    (65,  "Praia da Ilha de Tavira", "Continental", 37.1039, -7.6256),
    (89,  "Praia da Luz", "Continental", 37.0864, -8.7283),
    (67,  "Praia da Manta Rota", "Continental", 37.1639, -7.5206),
    (91,  "Praia da Marinha", "Continental", 37.0894, -8.4128),
    (70,  "Praia da Memória", "Continental", 41.2319, -8.7222),
    (26,  "Praia da Nazaré", "Continental", 39.6014, -9.0722),
    (77,  "Praia da Rainha (Cascais)", "Continental", 38.6994, -9.4181),
    (6,   "Praia da Redonda", "Continental", 41.3789, -8.7694),
    (75,  "Praia da Ribeira d'Ilhas", "Continental", 38.9878, -9.4189),
    (56,  "Praia da Rocha", "Continental", 37.117778, -8.535833),
    (87,  "Praia da Samoqueira", "Continental", 37.8689, -8.7914),
    (20,  "Praia da Tocha", "Continental", 40.3283, -8.8456),
    (71,  "Praia da Torreira", "Continental", 40.7589, -8.7183),
    (18,  "Praia da Vagueira", "Continental", 40.5639, -8.7656),
    (25,  "Praia da Vieira", "Continental", 39.8739, -8.9667),
    (51,  "Praia da Zambujeira do Mar", "Continental", 37.5225, -8.7881),
    (85,  "Praia das Furnas (Milfontes)", "Continental", 37.7189, -8.7936),
    (34,  "Praia das Maçãs", "Continental", 38.8256, -9.4703),
    (3,   "Praia de Afife", "Continental", 41.7758, -8.8767),
    (60,  "Praia de Armação de Pêra", "Continental", 37.1006, -8.3614),
    (61,  "Praia de Benagil", "Continental", 37.0872, -8.4239),
    (94,  "Praia de Cabanas (Tavira)", "Continental", 37.1306, -7.5922),
    (38,  "Praia de Carcavelos", "Continental", 38.679444, -9.334722),
    (90,  "Praia de Carvoeiro", "Continental", 37.0967, -8.4719),
    (79,  "Praia de Caxias", "Continental", 38.7011, -9.2736),
    (14,  "Praia de Cortegaça", "Continental", 40.9422, -8.6603),
    (88,  "Praia de Faro", "Continental", 37.0089, -7.9936),
    (82,  "Praia de Galapinhos", "Continental", 38.4844, -8.9622),
    (42,  "Praia de Galapos", "Continental", 38.4844, -8.9656),
    (8,   "Praia de Leça da Palmeira", "Continental", 41.1964, -8.7056),
    (9,   "Praia de Matosinhos", "Continental", 41.1764, -8.6917),
    (84,  "Praia de Melides", "Continental", 38.1283, -8.7906),
    (19,  "Praia de Mira", "Continental", 40.4536, -8.8028),
    (10,  "Praia de Miramar", "Continental", 41.0664, -8.6583),
    (1,   "Praia de Moledo", "Continental", 41.8491, -8.8744),
    (95,  "Praia de Monte Clérigo", "Continental", 37.3406, -8.8528),
    (68,  "Praia de Monte Gordo", "Continental", 37.1772, -7.4514),
    (52,  "Praia de Odeceixe", "Continental", 37.4417, -8.7989),
    (29,  "Praia de Peniche de Cima", "Continental", 39.3622, -9.3756),
    (64,  "Praia de Quarteira", "Continental", 37.0689, -8.1022),
    (21,  "Praia de Quiaios", "Continental", 40.2189, -8.8983),
    (69,  "Praia de Salgueiros", "Continental", 41.1189, -8.6656),
    (78,  "Praia de Santo Amaro de Oeiras", "Continental", 38.6856, -9.3083),
    (39,  "Praia de São João da Caparica", "Continental", 38.6525, -9.2436),
    (27,  "Praia de São Martinho do Porto", "Continental", 39.5089, -9.1383),
    (73,  "Praia de São Pedro de Moel", "Continental", 39.7589, -9.0303),
    (93,  "Praia de São Rafael", "Continental", 37.0756, -8.2806),
    (44,  "Praia de Troia-Mar", "Continental", 38.4908, -8.9056),
    (49,  "Praia de Vila Nova de Milfontes", "Continental", 37.7233, -8.7903),
    (2,   "Praia de Vila Praia de Âncora", "Continental", 41.8122, -8.8683),
    (63,  "Praia de Vilamoura", "Continental", 37.0733, -8.1189),
    (50,  "Praia do Almograve", "Continental", 37.6522, -8.7989),
    (55,  "Praia do Alvor", "Continental", 37.1239, -8.5989),
    (54,  "Praia do Amado", "Continental", 37.1689, -8.9022),
    (30,  "Praia do Baleal", "Continental", 39.3722, -9.3356),
    (66,  "Praia do Barril", "Continental", 37.0889, -7.6603),
    (4,   "Praia do Cabedelo (Viana)", "Continental", 41.6792, -8.8322),
    (23,  "Praia do Cabedelo (Figueira)", "Continental", 40.1389, -8.8583),
    (58,  "Praia do Camilo", "Continental", 37.0867, -8.6683),
    (46,  "Praia do Carvalhal", "Continental", 38.3039, -8.7889),
    (5,   "Praia do Castelo do Neiva", "Continental", 41.6167, -8.8167),
    (83,  "Praia do Creiro", "Continental", 38.4806, -8.9767),
    (86,  "Praia do Farol (Milfontes)", "Continental", 37.7267, -8.7917),
    (15,  "Praia do Furadouro", "Continental", 40.8756, -8.6756),
    (35,  "Praia do Guincho", "Continental", 38.7303, -9.4725),
    (32,  "Praia do Magoito", "Continental", 38.8689, -9.4503),
    (48,  "Praia do Malhão", "Continental", 37.7856, -8.7989),
    (80,  "Praia do Meco", "Continental", 38.4878, -9.1856),
    (72,  "Praia do Norte (Nazaré)", "Continental", 39.6106, -9.0833),
    (24,  "Praia do Pedrógão", "Continental", 39.9233, -8.9556),
    (43,  "Praia do Portinho da Arrábida", "Continental", 38.4792, -8.9833),
    (47,  "Praia do Porto Covo", "Continental", 37.8522, -8.7936),
    (36,  "Praia do Tamariz", "Continental", 38.7019, -9.3992),
    (7,   "Praia dos Fornos", "Continental", 41.3489, -8.7556),
    (76,  "Praia dos Pescadores (Ericeira)", "Continental", 38.9639, -9.4183),
    (31,  "Praia dos Supertubos", "Continental", 39.3422, -9.3656),
    (98,  "Complexo Balnear do Porto Moniz", "Madeira", 32.8689, -17.1689),
    (96,  "Praia Formosa", "Madeira", 32.6378, -16.9536),
    (97,  "Praia de Machico", "Madeira", 32.7189, -16.7656),
    (99,  "Praia do Porto Santo", "Madeira", 33.0583, -16.3353),
]

def reverse_geocode_bigdatacloud(lat, lon):
    """BigDataCloud reverse geocoding - free, no key needed."""
    url = (
        f"https://api.bigdatacloud.net/data/reverse-geocode-client"
        f"?latitude={lat}&longitude={lon}&localityLanguage=pt"
    )
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "Python/3"})
        with urllib.request.urlopen(req, timeout=10) as resp:
            return json.loads(resp.read().decode())
    except Exception as e:
        return {"error": str(e)}

def classify(data, lat, lon):
    if "error" in data:
        return "❓ ERRO", data["error"]

    country = data.get("countryCode", "")
    locality = data.get("locality", "") or ""
    city = data.get("city", "") or ""
    principal_subdivision = data.get("principalSubdivision", "") or ""
    local_name = data.get("localityInfo", {})
    
    # Check informative layers
    informative = data.get("localityInfo", {}).get("informative", [])
    administrative = data.get("localityInfo", {}).get("administrative", [])
    
    # Look for ocean/sea indicators in all layers
    all_layers = informative + administrative
    layer_names = [l.get("name", "").lower() for l in all_layers]
    layer_descs = [l.get("description", "").lower() for l in all_layers]
    
    water_keywords = {"ocean", "sea", "atlantic", "bay", "strait", "mar", "oceano", 
                      "atlântico", "atlântico norte", "north atlantic", "mediterranean"}
    
    for name in layer_names + layer_descs:
        for kw in water_keywords:
            if kw in name:
                # Could be ocean - but only if no country
                if not country:
                    return "🌊 MAR", f"Indicador de água: '{name}'"
    
    if not country:
        return "🌊 MAR", f"Sem país detectado (provavelmente oceano)"
    
    # Check if it's a beach/sand type
    for layer in all_layers:
        name = layer.get("name", "").lower()
        desc = layer.get("description", "").lower()
        if any(k in name or k in desc for k in ["beach", "praia", "sand", "areia", "dune", "duna"]):
            return "✅ PRAIA", f"Tipo identificado como praia: {layer.get('name')}"
    
    # Has country = on land somewhere
    place = locality or city or principal_subdivision or country
    return "🏝️ TERRA", f"País: {country}, Localidade: {place}"


results_by_status = {}
all_results = []

print(f"{'ID':>4}  {'Status':<14}  {'Nome':<47}  Detalhe")
print("-" * 130)

for beach_id, name, region, lat, lon in BEACHES:
    data = reverse_geocode_bigdatacloud(lat, lon)
    status, detail = classify(data, lat, lon)
    print(f"{beach_id:>4}  {status:<14}  {name:<47}  {detail}")
    all_results.append((beach_id, name, region, lat, lon, status, detail))
    results_by_status.setdefault(status, []).append((beach_id, name, lat, lon, detail))
    time.sleep(0.3)

print("\n" + "=" * 130)
print("RESUMO")
print("=" * 130)
for k, v in sorted(results_by_status.items()):
    print(f"  {k}: {len(v)} praias")

print("\n🌊 PONTOS POSSIVELMENTE NO MAR (necessitam correção urgente):")
for item in results_by_status.get("🌊 MAR", []):
    bid, name, lat, lon, detail = item
    print(f"  ID {bid:>3}: {name:<47}  coords: ({lat}, {lon})")
    print(f"          Detalhe: {detail}")
    print(f"          OSM: https://www.openstreetmap.org/?mlat={lat}&mlon={lon}&zoom=16")

print("\n🏝️  PONTOS EM TERRA (pode ser areia ou zona adjacente):")
for item in results_by_status.get("🏝️ TERRA", []):
    bid, name, lat, lon, detail = item
    print(f"  ID {bid:>3}: {name:<47}  coords: ({lat}, {lon})")

# Save JSON for later use
import json as jsonlib
with open("/tmp/beach_validation.json", "w") as f:
    jsonlib.dump(all_results, f, ensure_ascii=False, default=str, indent=2)
print("\nResultados guardados em /tmp/beach_validation.json")

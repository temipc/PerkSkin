# PerkSkin — Commerce / Marketplace Template

Ez a repository egy kereskedelmi célú, white‑label template a PerkSkin elnevezésű case-opening / marketplace rendszerhez. Nem kizárólag oktatási célra készült: célja, hogy gyorsan testreszabható alapot adjon termékek, dobozok és piactér funkciók felépítéséhez, melyet tovább lehet fejleszteni és értékesíteni.

Fontos: a projekt egy induló sablon — testreszabást, biztonsági és jogi ellenőrzést igényel, mielőtt élő rendszerként vagy termékként árusítanád.

## Elvárások (prerequisites)
- PHP 8.x telepítve (PDO és SQLite extension engedélyezve)
- Parancssor (PowerShell / Bash)

## Gyors lépések
1. Projekt gyökér: `\PerkSkin`
2. Hozd létre/seed-eld az SQLite adatbázist:

```bash
php database/seed.php
```
Ez létrehozza a `database/webdb.sqlite` fájlt és hozzáad egy admin felhasználót.

3. Indítsd a beépített PHP fejlesztői szervert (a `public` mappát szolgálja ki):

```bash
cd public
php -S localhost:8000
```

4. Nyisd meg a böngészőt és navigálj az alábbi URL-re:

```
http://localhost:8000
```

## Teszt felhasználó (demo)
- Email: `admin@admin.com`
- Jelszó: `admin123`

Ez a demo fiók gyors tesztelésre szolgál. Ha a sablont értékesíteni szándékozod, javasolt eltávolítani vagy megváltoztatni a seedelt admin fiókokat és minden hardkodolt jelszót.

## Gyakori parancsok és ellenőrzések
- API ellenőrzés (pl. fordítások):

```bash
curl "http://localhost:8000/index.php?page=api&action=listTranslations"
```

- Ellenőrizd, hogy az adatbázis fájl létezik:

```bash
ls -la database/webdb.sqlite
```

- Ha hibák jelennek meg a konzolban (500-as választ ad a backend), nézd meg a PHP error logot vagy a terminál kimenetét, ahol a beépített szervert indítottad.

## Gyakori problémák és javítások
- "unable to open database file": ellenőrizd, hogy a `database/webdb.sqlite` létezik-e és a PHP folyamat írhat/olvashatja.
- Szintaxis/SQL hibák: ha `FOR UPDATE`-t talál a kódban, SQLite nem támogatja — azt eltávolítottuk vagy módosítani kell.
- API 401 "unauthorized": győződj meg róla, hogy be vagy jelentkezve (session cookie-k), vagy használj a felületen bejelentkezést az admin adatokkal.

## Functionális megjegyzések (template)

- A projekt egy induló sablon (template) white‑label marketplace / case‑opening szolgáltatásokhoz. Gyors prototípus-készítésre, proof‑of‑conceptekhez vagy termékalapnak egy kereskedelmi megoldáshoz alkalmas.

## Használati célok (példák)

- Fejlesztható piactér vagy "case opening" szolgáltatás (játékbeli tárgyak, kuponok, licencek kezelése).
- White‑label megoldás játékokhoz, promóciókhoz vagy digitális termékek kereskedelméhez.
- SaaS szolgáltatás alapja: multi‑tenant vagy single‑tenant kiadáshoz adaptálható.
- Gyors prototípus és demo értékesítéshez (a front‑end és backend alapfunkciói rögtön használhatók).

## Eladásra előkészítés — gyors checklist

1. Távolítsd el a seedelt demo fiókokat (pl. `admin@admin.com`) vagy változtasd meg a jelszavakat.
2. Konfiguráld a production környezetet: HTTPS, környezeti változók, biztonságos jelszó- és session kezelés.
3. Dokumentáld a telepítést és a szükséges konfigurációs lépéseket a vásárlók számára.
4. Add meg a licenc feltételeit és a support/karbantartási csomagokat.
5. Gondoskodj fizetési integrációról (ha szükséges) és jogi megfelelésről (ÁSZF, adatkezelés).

## Hibajelenség: "gyors eladás" (Quick Sell) nem működik

- Ha doboz megnyitás után a "Gyors eladás" nem működik, gyakori okok:
  - A `perksin_user_inventory` vagy `perksin_wallet_transactions` tábla hiánya (futtasd `php database/seed.php`).
  - Backend API hiba (500): nézd meg a terminál kimenetet, ahol a PHP szerver fut, vagy a böngésző DevTools Network fülét az API válasz (`/index.php?page=api&action=recordCaseHistory`).

Ha a probléma a kódból ered (SQL szintaxis vagy hiányzó `perksin_` prefix), ellenőrizd az [app/Controllers/ApiController.php](app/Controllers/ApiController.php) és [app/Controllers/CaseController.php](app/Controllers/CaseController.php) fájlokat.

## Funkciólista (Feature matrix)
A következő lista röviden összegzi, mely funkciók érhetők el az alkalmazásban, melyek részben működnek és melyek csak UI/placeholder vagy hiányoznak. Ez a lista fejlesztési/oktatási célra készült.

- **Hitelesítés**
  - Login (email/password): Implementálva (form és API) — Működik
  - Register: Implementálva — Működik
  - Logout: Implementálva — Működik
  - Remember me: UI elem van, szerveroldali tartós bejelentkezés nincs — Részben
  - Two-factor auth (2FA / TOTP): Implementálva (setup QR-kóddal vagy manuális secret-tel, majd 6 jegyű kód ellenőrzése) — Működik

- **Felhasználói profil**
  - Megtekintés / szerkesztés (getProfile/saveProfile API): Implementálva — Működik
  - Avatar feltöltés: Nincs implementálva — Nincs

Megjegyzés: a seedelt demo admin fióknál (`admin@admin.com`) a 2FA szándékosan le van tiltva, ezért a funkciót normál felhasználói fiókkal érdemes tesztelni.

- **Pénzügy / Wallet**
  - walletBalance: Implementálva — Működik
  - walletTransactions: Implementálva — Működik
  - walletAdjust / Top-up (szimulált): Implementálva (API) — Működik (nem valódi fizetés)
  - Valódi fizetési gateway integráció: Nincs — Nincs

- **Gems**
  - gemsBalance / gemsAdjust / gemTransactions: Implementálva — Működik

- **Spinner / Spins**
  - Spin state (spinState) és adjust (spinAdjust): Implementálva — Működik
  - UI spinner: Implementálva — Működik

- **Dobozok (Cases)**
  - Lista és megjelenítés: Implementálva — Működik
  - Doboz megnyitás / recordCaseHistory: Implementálva — Működik
  - Quick sell (gyors eladás): Szerveroldali folyamat támogatott (wallet tx + history) — Működik (fixelve)
  - Készlet / kód kezelés (item codes): Részben implementálva (támogatás van), UI/flow lehet részben hiányos — Részben

- **Tartalom és fordítások**
  - Content sections (list/save): Implementálva — Működik
  - Translations (DB-backed overrides): Implementálva — Működik

- **Admin / Moderáció**
  - Admin API végpontok: Több admin API létezik (list/save content, cases, products) — Működik API szinten
  - Admin GUI (teljes körű felület minden művelethez): Nincs teljesen kész — Részben
  - Ranks / Badges (adatmodell): Adatbázisban megvannak; UI vezérlés hiányos — Részben

- **Külső bejelentkezések / Social**
  - Steam / Google / Discord / Facebook gombok: UI-ban jelen vannak, de integrációk nem működnek — Nincs

- **Egyéb**
  - Events / Missions / Exchange nézetek: Frontend megvannak, backend logika részben statikus vagy hiányos — Részben
  - Valódi chat / realtime funkciók: UI elemek vannak, backend nincs (placeholder) — Nincs

Ha fel szeretnéd tüntetni a GitHub README-ben ezt a mátrixot, vagy részletesebben szeretnéd jelölni a hiányzó/tervezett elemeket, segítek formázni és kibővíteni a leírást.

## További lépések fejlesztőknek
- Kód szerkesztése után frissítsd a böngészőt, vagy indítsd újra a PHP szervert.
- Forráskód módosításai után érdemes futtatni a `database/seed.php`-t csak ha új táblákat szeretnél létrehozni — ne futtasd éles adat veszteség esetén.

---
Kérdésed van a beállítással kapcsolatban vagy szeretnéd, hogy lefuttassam helyetted a szervert/teszteket, szólj nyugodtan.

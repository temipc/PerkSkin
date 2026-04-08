# PerkSkin Felhasználói Dokumentáció

## 1. Mi a PerkSkin

A PerkSkin egy promóciós és engagement platform, ahol a felhasználó különböző dobozokat, főoldali pörgetéseket és marketplace funkciókat használva kedvezményeket, promóciós ajánlatokat, badge-eket és csomagokat nyerhet.

A jelenlegi implementáció célja:

- marketing aktivitás növelése
- játékosított felhasználói élmény
- promóciók és kuponjellegű ajánlatok nyereményként történő kiosztása
- aktivitás- és visszatérésösztönzés
- inventory és marketplace alapú további elkötelezés

## 2. Fő fogalmak

### 2.1. Termék

A termék a rendszer alap nyereménytípusa. Ez lehet például:

- `Cinema -10%`
- `Fuel 3%`
- egy badge jellegű jutalom
- egy bundle jellegű jutalom

Minden termékhez tartozhat:

- név
- ár vagy becsült érték USD-ben
- kategória
- típus
- leírás
- érvényességi időablak
- főoldali spinnerben való használhatóság

### 2.2. Kategória

A kategória logikai csoportosítás. Példák:

- Entertainment
- Fuel
- Badge
- Bundle

A kategória arra jó, hogy:

- admin oldalon strukturált legyen a terméklista
- dobozhoz ne csak egyedi termék, hanem teljes kategória is hozzáadható legyen
- marketing logikában szűrhető legyen a kínálat

### 2.3. Bundle

A bundle több termékből összeállított csomag.

Példa:

- `Starter Saver Bundle`
- tartalma:
  - `Cinema -10%`
  - `Fuel 3%`

A bundle használható:

- külön nyereményként
- doboz tartalmában
- főoldali bundle blokkban
- spinner rewardként, ha a hozzá tartozó termék rekord bundle típusú

### 2.4. Badge

A badge speciális jutalomtípus. Két szinten jelenik meg:

- mint külön `badge` típusú nyeremény a termékkatalógusban
- mint külön badge admin rekord a badge táblában

A badge alkalmas:

- státuszjelzésre
- hűségprogramhoz
- kampányhoz kötött gyűjtésre
- későbbi rang- vagy jogosultságrendszerhez

### 2.5. Doboz

A doboz nyereményforrás. A felhasználó ezt nyitja meg.

Egy dobozhoz tartozik:

- cím
- ár
- required level
- tag
- risk
- slug
- kép
- szekcióbesorolás

A doboz tartalma lehet:

- egyedi termék
- teljes kategória
- teljes bundle

### 2.6. Event

Az event időzített kampányesemény.

Egy eventhez tartozik:

- cím
- leírás
- kezdő időpont
- záró időpont
- szín
- opcionális link

Az eseményoldali naptár egy napra több eseményt is tud kezelni.

## 3. Fő felhasználói felületek

### 3.1. Főoldal

A főoldal fő részei:

- hero blokk
- live drop
- spinner
- normál dobozlista
- közösségi dobozok
- kiemelt dobozok
- bundle rewards blokk
- event dobozok
- chat demo blokk

### 3.2. Single Case oldal

Itt látható:

- az adott doboz adatai
- ár
- risk
- required level
- tartalomlista
- napi nyitási feltételek
- pörgetési és nyerési folyamat

### 3.3. Dashboard

A dashboard fő részei:

- Overview
- Profile
- Languages
- Content
- Events
- Catalog

## 4. Hogyan működik a főoldali spinner

A főoldali spinner külön engagement elem, nem ugyanaz, mint a doboznyitás.

Jellemzők:

- napi limitált használat
- shard/collectible rendszer
- bónusz pörgetések
- nyeremény és nem-nyeremény kimenet

### 4.1. Sikertelen pörgetés

A spinnerben van kötelező vesztes kimenet is.

Jelenlegi logika:

- van `No win` mező
- ez a rewardokhoz képest emelt súllyal szerepel
- vesztes pörgetés után automatikus ingyenes újrapörgetés indul
- ez nem külön nyereménymodal, hanem belső reroll

### 4.2. Spinner nyereménytípusok

A spinner használhat:

- normál terméket
- badge típusú terméket
- bundle típusú terméket
- collectible/shard elemet

### 4.3. Shard és bonus spin

A shard célja:

- gyűjthető engagement token
- 10 shard után bonus spin jár

Ez visszatérésre és ismételt használatra ösztönöz.

## 5. Szintek

A rendszerben `Lv 1`-től `Lv 10`-ig terjedő szintlogika van.

A szint célja:

- doboznyitási korlátozás
- progresszió érzékeltetése
- promóciós és jutalmazási szintezés

A szint felhasználása:

- egyes dobozok csak adott szint felett nyithatók
- a UI jelzi a szükséges szintet
- admin oldalról manuálisan is felülírható

### 5.1. Jelenlegi működés

A szint számolása és tárolása:

- vendégnél DB-s kliensállapotban
- bejelentkezett felhasználónál szintén DB-s kliensállapotban
- automatikus vagy manuális mód

## 6. Gems és pénz

### 6.1. Wallet / pénzegyenleg

A wallet USD-alapú belső egyenleg.

Erre használjuk:

- doboznyitás elszámolása
- quick sell jóváírás
- tranzakciós napló

Jellemzők:

- szerveroldali wallet táblák
- külön wallet transaction ledger
- dashboard overview-ben is látszik

### 6.2. Gems

A gem külön erőforrás.

Felhasználási cél:

- játékosított jutalomlogika
- promóciós és engagement mechanika
- nem klasszikus pénzügyi egyenleg, hanem külön reward channel

Miért jó:

- olcsóbb pszichológiai jutalmazási egység
- kampányokhoz jól használható
- pörgetéses és achievement rendszerhez illeszkedik

### 6.3. Pénz és gem különbsége

A pénz:

- tényleges belső vásárlási és elszámolási egység
- dobozokhoz és eladásokhoz kapcsolódik

A gem:

- engagement és reward token
- nem ugyanaz a szerepe, mint a walletnek

## 7. Inventory és history

### 7.1. History

A history a nyitások és elszámolások naplója.

Példák:

- claimed
- sold

Ez kell:

- auditálhatósághoz
- szintszámításhoz
- dashboard statisztikákhoz

### 7.2. Inventory

Az inventory a megtartott nyereményeket tartalmazza.

Ez kell:

- felhasználói készletkezeléshez
- exchange/marketplace folyamathoz
- későbbi beváltási logikához

## 8. Marketplace / Exchange

Az exchange oldal célja:

- inventory elemekkel ajánlat létrehozása
- nyitott ajánlatok kezelése
- peer-to-peer vagy későbbi moderált cserepiac előkészítése

Jelenlegi állapot:

- új ajánlat létrehozható
- nyitott ajánlatok listázhatók
- saját ajánlat lezárható

## 9. Eseménynaptár

Az eseményoldal célja:

- kampányok időbeli áttekintése
- kattintható promóciós események
- több esemény kezelése azonos napra

Jelenlegi működés:

- alapértelmezett nézet: hónap
- mindig aktuális hónapról indul
- percre pontos kezdő és záró idő kezelhető
- többnapos esemény több napon is megjelenik
- ha kevés esemény fér ki, szöveggel jelenik meg
- ha sok van, színes jelölésekkel jelenik meg
- kattintásra részletező modal nyílik

## 10. Profil és biztonság

Felhasználói profilban elérhető:

- megjelenített név
- nyelv
- pénznem
- számlázási adatok
- szállítási adatok
- munkamenetek listája
- 2FA

### 10.1. Kétfaktoros hitelesítés

Van TOTP-alapú 2FA támogatás.

Használat:

- QR-kód beolvasás
- manuális secret
- 6 számjegyű kód megerősítés

## 11. Fordítások

A fordítási rendszer két rétegű:

- fájlos alapfordítás
- DB-s felülírás

Ez azt jelenti:

- az alap kulcsok forrásfájlban élnek
- az admin a DB-ben felülírhatja őket
- a felület a DB-s értéket fogja használni, ha létezik

## 12. Vendég és bejelentkezett mód

### 12.1. Vendég

A rendszer vendégként is működik, de korlátozottabban.

Vendégnél:

- kliensállapot DB-ben tárolódik
- spinner működik
- bizonyos flow-k korlátozottak

### 12.2. Bejelentkezett felhasználó

Bejelentkezve elérhető:

- wallet
- gems
- history
- inventory
- profile
- sessions
- marketplace teljesebb használata
- 2FA

## 13. Admin funkciók

Admin tudja kezelni:

- dobozok
- termékek
- kategóriák
- bundle-ök
- badge-ek
- eventek
- content szekciók
- fordítások
- kézi rang/szint felülírás

## 14. Mit jelent a slug

A slug URL-barát azonosító.

Példa:

- cím: `Neon Rush`
- slug: `neon-rush`

Ez kell:

- stabil linkekhez
- keresőbarát URL-hez
- egyedi oldalbetöltéshez

## 15. Jelenlegi MVP korlátok

A mostani rendszer több helyen már üzemszerű logikát követ, de vannak MVP-elemek is.

Példák:

- missions oldal jelenleg placeholder jellegű
- exchange oldal működő alapfolyamat, de még nem teljes kereskedési rendszer
- badge-ek profil- vagy jogosultsági logikába kötése még bővíthető

## 16. Rövid használati példák

### 16.1. Admin termék létrehozása

1. Dashboard
2. Catalog
3. Products
4. New
5. Név, kategória, típus, érték, időablak, leírás
6. Save

### 16.2. Admin bundle létrehozása

1. Dashboard
2. Catalog
3. Bundles
4. New
5. Név mentése
6. Bundle itemek hozzáadása

### 16.3. Event létrehozása

1. Dashboard
2. Events
3. New
4. Start és End megadása percre pontosan
5. Cím, leírás, szín, link
6. Save

### 16.4. Dobozhoz bundle hozzárendelése

1. Dashboard
2. Catalog
3. Boxes
4. Edit
5. Case contents
6. Type = Bundle
7. Source = kívánt bundle
8. Save

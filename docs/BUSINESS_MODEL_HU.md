# PerkSkin Üzleti Modell és Rendszerkapcsolatok

## 1. Üzleti cél

A PerkSkin nem klasszikus lootbox-platformként értelmezendő, hanem promóciós ajánlatok, kedvezmények, kuponok és marketing jutalmak játékosított kiosztási rendszerének.

A platform fő üzleti céljai:

- felhasználószerzés
- visszatérés ösztönzése
- promóciók játékosított terítése
- partnerajánlatok láthatóvá tétele
- inventory és cserepiac alapú elkötelezés
- kampányalapú engagement

## 2. Magas szintű modell

Az üzleti logika egyszerűsítve:

1. Partner vagy admin létrehoz promóciós ajánlatokat.
2. Az ajánlatokból termékek, bundle-ök, badge-ek és doboztartalmak épülnek.
3. A felhasználó pörget, dobozt nyit, gyűjt, szintet lép.
4. A nyereményt megtartja, eladja vagy exchange ajánlatba teszi.
5. A rendszer közben adatot gyűjt engagementről, kampányhasználatról és aktivitásról.

## 3. Fő üzleti entitások

## 3.1. Partner

A partner biztosítja a felhasználható promóciós értéket.

Példák:

- mozi
- benzinkút
- kiskereskedelmi partner
- kuponpartner

Partnerhez kapcsolódnak az ajánlatok.

## 3.2. Offer / termék

Ez az üzleti alapegység.

Üzleti jelentése:

- egy konkrét nyerhető promóció
- egy kedvezmény vagy jutalom
- egy értékkel bíró kampányelem

Példák:

- `Cinema -10%`
- `Fuel 3%`
- `VIP Bronze Badge`

## 3.3. Kategória

A kategória üzleti szegmentálásra való.

Haszna:

- kampánycsoportosítás
- riportálás
- dobozok összeállítása kategóriánként
- célzott promóciók

## 3.4. Bundle

A bundle több ajánlatból álló kombinált nyeremény.

Üzleti haszna:

- magasabb érzékelt érték
- csomagolt promóció
- cross-sell
- több partner egyszerre promotálható

Példa:

- mozi + üzemanyag kedvezmény egyetlen csomagban

## 3.5. Badge

A badge nem feltétlen közvetlen gazdasági érték, hanem reputációs vagy gamification érték.

Üzleti haszna:

- státuszjelölés
- gyűjtési motiváció
- hűségprogram
- achievement alapú engagement

## 3.6. Doboz

A doboz kampánycsomag vagy gamified entry point.

Üzleti szerepe:

- promóciók attraktív csomagolása
- eltérő kockázati és árszintek kezelése
- szintalapú hozzáférés
- tematizált nyereményelosztás

## 3.7. Event

Az event időalapú kampányszervező egység.

Üzleti haszna:

- kampánynaptár
- szezonális aktivitás
- korlátozott idejű promóció
- időzített kommunikáció

## 4. Kapcsolatok egymás között

## 4.1. Termék -> kategória

Egy termék egy kategóriába tartozhat.

Cél:

- tematizálás
- szűrés
- admin kezelhetőség

## 4.2. Bundle -> több termék

Egy bundle több terméket tartalmaz.

Cél:

- nagyobb perceived value
- kombinált ajánlat
- többes partnerpromóció

## 4.3. Doboz -> tartalomforrás

Egy doboz tartalma háromféle forrásból épülhet:

- konkrét termék
- teljes kategória
- teljes bundle

Ez azért fontos, mert így a dobozlogika nem statikus.

## 4.4. Event -> időablak

Az event időtartománya meghatározza:

- mikor látszik kampányként
- mikor releváns kommunikációs elemként
- milyen napokon jelenik meg a naptárban

## 4.5. Felhasználó -> history -> inventory

A felhasználó aktivitásából keletkezik:

- history
- inventory
- rang/szint
- engagement állapot

## 5. Monetizáció és gazdasági logika

## 5.1. Wallet

A wallet a rendszer elsődleges monetizációs tengelye.

Szerepe:

- doboznyitás díja
- quick sell jóváírás
- tranzakciós audit

Miért kell:

- pontos elszámolás
- user LTV mérés
- pénzügyi riport

## 5.2. Gems

A gem második gazdasági tengely, de nem ugyanaz, mint a pénz.

Szerepe:

- engagement token
- kampány és reward mechanika
- játékosított célok

Miért üzletileg hasznos:

- nem pénzügyi egységként könnyebb jutalmazni
- növeli az aktivitást anélkül, hogy minden reward közvetlen pénzügyi költség lenne
- külön promóciós réteget ad

## 5.3. Quick sell

A quick sell a nyert elem azonnali monetizálása.

Üzleti előny:

- folyamatos visszaforgatás a rendszerbe
- csökkenti a felhasználói döntési súrlódást
- több újranyitást ösztönöz

## 6. Gamification modell

## 6.1. Spinner

A spinner egy napi engagement eszköz.

Üzleti szerep:

- napi visszatérés
- session indítás
- könnyű belépési pont
- reward expectation fenntartása

Jelenlegi logika:

- napi limit
- collectible/shard
- bonus spin
- kötelező vesztes kimenet is van
- vesztes után automatikus újrapörgetés indul

Ez pszichológiailag fontos:

- nem minden spin azonnali jutalom
- mégsem szakad meg a flow
- növeli a “még egyszer megpróbálom” élményt

## 6.2. Szintek

A szintrendszer üzleti szerepe:

- tartalomkapuzás
- progresszió
- státuszérzet
- megtartás

Szintek:

- `Lv 1` - `Lv 10`

Felhasználás:

- dobozhozzáférés
- nehézségi és értékszegmentálás

## 6.3. Badge-ek

A badge-ek üzleti szerepe:

- gyűjthetőség
- státusz
- loyalty mechanika
- kampányjelvény

## 7. Marketing használati esetek

## 7.1. Kuponkiosztás játékosítva

Ahelyett, hogy a rendszer csak sima kuponlistát mutatna, a kedvezmény nyereményként érkezik.

Előny:

- magasabb engagement
- jobb CTR
- jobb kampányemlékezet

## 7.2. Kampányidőszakos események

Az eventekkel lehet:

- Black Friday kampány
- szezonális ajánlat
- hétvégi boost
- partnerakció

## 7.3. Kategóriaalapú promóció

Példa:

- csak fuel ajánlatokból álló kampánydoboz
- csak entertainment bundle
- vegyes családi promóció

## 7.4. Bundle alapú upsell

A bundle jobb perceived value-t ad, mint az egyedi termék.

Példa:

- `Starter Saver Bundle`
- egyszerre több kedvezmény
- nagyobb értékérzet, erősebb kampányajánlat

## 8. Admin működési modell

Az admin oldal célja, hogy a kampánymenedzser vagy üzleti üzemeltető kódírás nélkül tudja kezelni a kínálatot.

Admin feladatok:

- termék létrehozása
- időtartam beállítása
- leírás és felhasználási feltételek megadása
- kategória létrehozása
- bundle építése
- doboz összeállítása
- event időzítése
- fordítások kezelése

## 9. Mi mire jó röviden

### 9.1. Termék

Konkrét nyerhető promóciós egység.

### 9.2. Kategória

Tematikus csoportosítás és tömeges dobozépítés.

### 9.3. Bundle

Több termékből álló csomagolt ajánlat.

### 9.4. Badge

Státusz- és gyűjtési jutalom.

### 9.5. Doboz

Gamified hozzáférési pont a nyereményekhez.

### 9.6. Event

Időzített kampány és kommunikációs blokk.

### 9.7. Wallet

Pénzügyi elszámolási réteg.

### 9.8. Gems

Engagement és reward réteg.

### 9.9. Inventory

A megtartott nyeremények készlete.

### 9.10. Exchange

Másodlagos aktivitási és cserefolyamat.

## 10. Jelenlegi rendszerkapcsolatok táblázatos logikában

`Partner` -> ad `Offer/Termék`

`Termék` -> tartozhat `Kategóriához`

`Bundle` -> több `Terméket` tartalmaz

`Doboz` -> tartalmazhat `Terméket`, `Kategóriát` vagy `Bundle-t`

`Felhasználó` -> nyit `Dobozt`

`Doboznyitás` -> létrehoz `History` rekordot

`Claimed` -> bekerül `Inventory`-ba

`Sold` -> jóváírhat `Wallet` összeget

`Spinner` -> adhat `Terméket`, `Badge` típusú terméket, `Bundle` típusú terméket vagy `Shardot`

`Shard` -> adhat `Bonus spin`-t

`Event` -> időben megjelenít kampányt és forgalmat terel

## 11. Ajánlott üzleti használat

### 11.1. MVP szint

Használat:

- alap promóciós dobozok
- napi spinner
- szezonális eventek
- egyszerű bundle kampányok

### 11.2. Növekedési szint

Használat:

- partnerenként külön kampánydobozok
- badge-es hűségprogram
- szinthez kötött exkluzív ajánlatok
- kategóriaalapú personalizáció

### 11.3. Haladó szint

Használat:

- inventory alapú beváltás
- kuponkód és készletlogika
- partner-specifikus validitás és feltételek
- gamified CRM kampányok

## 12. Jelenlegi fontos megjegyzések

- A platform jelenleg már erősen DB-alapú.
- A `localStorage` csak gyorsítótár jellegű, nem személyes tartalomra maradt.
- A missions rész jelenleg MVP placeholder.
- Az exchange alapfolyamat működik, de tovább bővíthető valódi licit/elfogadás logikára.

## 13. Végső üzleti értelmezés

A PerkSkin lényege nem az, hogy “véletlen jutalmakat ad”, hanem az, hogy:

- promóciós ajánlatokat
- kedvezményeket
- kampánycsomagokat
- státusz és loyalty elemeket

játékosított, visszatérésre ösztönző rendszerben adjon át.

Ezért a platform egyszerre:

- marketingeszköz
- engagement motor
- kampányterítő felület
- promóciós katalógus
- gamified loyalty réteg

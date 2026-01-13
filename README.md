# Voorraadbeheersysteem

Web-based voorraadbeheer voor technische materialen. Ontwikkeld voor intern gebruik bij Reme Techniek, gericht op inzicht, controle en logging van voorraden.

---

## Overzicht

- Actuele voorraden per materiaal
- Zoeken en filteren op type, leverancier, omschrijving, categorie, voorraad en minimum
- Waarschuwingen bij lage voorraad (onder minimum)
- Logboek met alle voorraadmutaties
- Rolgebaseerde toegang en beheertaken

---

## Functionaliteiten

- Inloggen met gebruikersaccount
- Dashboard met:
  - Overzicht materialen (kleine lijst + bulkacties voor bevoegde rollen)
  - Lage voorraad (items strikt onder minimum)
  - Recente logboekregels
- Materialen beheren:
  - Bewerken (naam, type, leverancier, omschrijving, minimum, categorie, foto, SKU)
  - Voorraadmutaties: “Pakken” (afhalen) en “Bijleggen” (toevoegen)
  - Bulkacties voor pakken/bijleggen; bulk verwijderen alleen door admin
  - Verwijderen: enkel admin
- Volledig logboek van voorraadmutaties
- Gebruikersbeheer: wachtwoord resetten (admin)

---

## Rollen & rechten

- **Admin**: volledige rechten (gebruikers, materialen, verwijderen, bulk verwijderen)
- **Projectleider**: materialen beheren (maken/bewerken), voorraad muteren; geen verwijderen
- **Medewerker**: alleen bekijken

Op dit moment worden rechten afgedwongen in controllers en views. Een toekomstige stap is migratie naar Laravel Policies.

---

## Data & model

Belangrijkste tabellen: `products`, `stocks`, `stock_logs`, `categories`, `users`.

- `products`: basisinfo (naam, type, leverancier, omschrijving, minimale_voorraad, foto_url, categorie_id, optioneel SKU, soft deletes)
- `stocks`: actuele voorraad per product (`aantal`, laatst aangepast door/op)
- `stock_logs`: historiek van mutaties (pakken/bijleggen), met gebruiker en opmerking
- `categories`: materiaalcategorieën
- `users`: gebruikers met rol

Zie ook het JSON schema: `docs/database-schema.json`.

---

## Installatie (Windows / XAMPP)

Voorwaarden:
- PHP 8.2+
- Composer
- Node.js 18+
- XAMPP (Apache + MySQL), of SQLite

Stappen:

```bash
# 1) Repository clonen
git clone <repo-url>
cd voorraadbeheersysteem

# 2) Dependencies installeren
composer install
npm install

# 3) .env aanmaken en app key genereren
cp .env.example .env
php artisan key:generate

# 4a) SQLite (snel voor ontwikkeling)
# In .env: DB_CONNECTION=sqlite en zet pad naar database/database.sqlite
# Maak het bestand als het nog niet bestaat:
type NUL > database\database.sqlite

# 4b) MySQL (alternatief)
# In .env: DB_CONNECTION=mysql, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD invullen

# 5) Migrations + seeders
php artisan migrate:fresh --seed

# 6) Assets in ontwikkeling
npm run dev

# 7) Server starten (indien niet via XAMPP Apache)
php artisan serve
```

---

## Gebruik

- Inloggen en navigeer naar Materialen of Dashboard.
- Zoeken: gebruik de zoekbalk (`q`) of filters:
  - Type, Leverancier, Categorie (`onder_min` / `boven_min`), algemene zoekterm
- Acties per materiaal:
  - “Pakken” / “Bijleggen” via modals
  - Bewerken (admin/projectleider)
  - Verwijderen (alleen admin)
- Bulkacties (admin/projectleider): selecteer meerdere materialen en kies actie; bulk verwijderen alleen door admin.

---

## Ontwikkeling & productie

Ontwikkeling:
- `npm run dev` voor Vite dev server
- `php artisan route:list` om routes te controleren
- `php artisan optimize:clear` bij config/routing wijzigingen

Productie build en optimalisaties:

```bash
# Build assets
npm run build

# Laravel caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Composer optimalisatie (zonder dev)
composer install --no-dev --optimize-autoloader
```

Aanbevolen: zet `APP_ENV=production` en `APP_DEBUG=false`, en enable PHP OPcache.

---

## Seeder

- `MaterialenSeeder` maakt meerdere categorieën en 100 producten aan, willekeurig verdeeld over categorieën.
- Voorraad wordt geïnitialiseerd met een willekeurige waarde (0–20).

---

## Bekende aandachtspunten

- “Lage voorraad” toont items strikt onder `minimale_voorraad`.
- Zoeken gebruikt de `q`-parameter (type, leverancier, omschrijving, naam, categorie; numeriek matcht exact op voorraad en minimum).
- Verwijderen is beperkt tot admin; UI-knoppen zijn verborgen voor niet-admins.

---

## Licentie

Intern gebruik binnen Reme Techniek. Vraag de beheerder voor externe distributie of aanpassingen.

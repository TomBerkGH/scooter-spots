# AI-richtlijnen voor Scooter Spots

Lees dit bestand voordat je code onderzoekt of wijzigt. Deze richtlijnen gelden voor de hele repository.

## Over de applicatie

Scooter Spots is een Laravel-applicatie waarmee gebruikers mooie of handige plekken tijdens een scooterrit kunnen vastleggen en terugvinden. Een spot kan een foto, naam, notitie, GPS-coördinaten en tags bevatten. De publieke homepage toont alle gedeelde spots. Ingelogde gebruikers beheren hun eigen plekken, inclusief zoeken, toevoegen, wijzigen en verwijderen.

De applicatie gebruikt:

- Laravel 13 en PHP 8.5 voor de backend;
- Inertia.js met Vue 3 en TypeScript voor de interface;
- Tailwind CSS voor styling;
- Fortify voor authenticatie;
- lokale storage of Cloudflare R2 voor spotfoto’s;
- Nominatim/OpenStreetMap voor aanvullende locatiegegevens;
- Pest, PHPUnit, PHPStan/Larastan, Pint, ESLint, Prettier en `vue-tsc` voor kwaliteit.

## Architectuur en mappenstructuur

Houd verantwoordelijkheden gescheiden en volg de bestaande indeling:

```text
app/
  Actions/       Muterende use-cases, één duidelijke actie per class
  Queries/       Leeslogica en zoekopdrachten
  Data/          Getypeerde data-objecten tussen lagen
  Http/Requests/ Validatie en normalisatie van HTTP-input
  Http/Controllers/ Dunne adapters tussen HTTP/Inertia en de applicatielaag
  Services/      Integraties en technische domeinservices
  Contracts/     Kleine interfaces voor verwisselbare afhankelijkheden
  Models/        Eloquent-modellen, relaties, casts en eenvoudige modelregels
resources/js/
  pages/         Dunne Inertia-pagina’s die de schermflow samenstellen
  partials/      Herbruikbare Vue-schermdelen en feature-UI
  components/ui/ Generieke UI-primitives; houd deze domein-onafhankelijk
  layouts/       Pagina- en applicatieshells
  composables/   Herbruikbare Vue-state en composable gedrag
  lib/           Kleine framework-onafhankelijke helpers
  types/         Gedeelde TypeScript-types
```

Gebruik voor nieuwe feature-UI `resources/js/partials/<feature>/`. Een page mag props ontvangen, state van de pagina coördineren en partials samenstellen, maar hoort geen groot blok domein- of presentatiemarkup te worden. Bestaande domeincomponenten onder `components/spots/` mogen blijven staan; verplaats ze alleen wanneer je die feature toch doelgericht aanpast. Generieke componenten blijven onder `components/ui/`.

De gebruikelijke backend-flow is:

```text
Route → Controller → Form Request → Data object → Action/Query → Model/Service
```

Controllers geven geen domeinlogica door elkaar met HTTP-afhandeling. Gebruik een `Action` voor een muterende use-case en een `Query` voor complexe of herbruikbare leeslogica. Gebruik services voor externe systemen, opslag of een afzonderlijke technische verantwoordelijkheid. Laat een Eloquent-model niet uitgroeien tot een servicecontainer of use-case-orchestrator.

## SOLID is de standaard

Pas SOLID pragmatisch toe; voeg geen abstracties toe zonder duidelijke verantwoordelijkheid.

### Single Responsibility

- Geef elke class en functie één reden om te veranderen.
- Houd controllers dun: autorisatie/input ophalen, een use-case aanroepen en een response teruggeven.
- Scheid validatie, data-transformatie, businessregels, opslag en externe API-calls.
- Splits grote Vue-pages op in kleine partials met één UI-verantwoordelijkheid.

### Open/Closed

- Breid gedrag uit via een bestaand contract, een nieuwe service of een nieuwe action wanneer dat past.
- Vermijd conditionele vertakkingen waarin meerdere externe providers of opslagmechanismen hardcoded staan.
- Houd configuratie in `config/` en `.env`; hardcode geen deployment-specifieke waarden.

### Liskov Substitution

- Een implementatie van een contract moet zich gedragen volgens het volledige contract, inclusief foutafhandeling en returntypes.
- Gebruik interfaces alleen wanneer er werkelijk meerdere implementaties, een externe grens of een testbare dependency is.

### Interface Segregation

- Maak kleine, doelgerichte interfaces. Voeg geen ongerelateerde methodes toe aan een bestaand contract.
- Laat consumers alleen afhankelijk zijn van de methodes die ze nodig hebben.

### Dependency Inversion

- Injecteer dependencies via constructors of methode-injectie en laat Laravel de concrete implementaties resolven.
- Maak externe clients, storage-diskkeuzes en tijdsafhankelijke services niet rechtstreeks aan in businesslogica.
- Gebruik bijvoorbeeld een contract zoals `EnrichesSpotLocation` op de grens met locatieverrijking.

## Backend-richtlijnen

- Gebruik typed properties, parameters en returntypes. Voeg waar nodig PHPDoc toe voor Eloquent-relaties en generics zodat PHPStan de intentie begrijpt.
- Gebruik Form Requests voor requestvalidatie en Data-objecten voor getypeerde overdracht naar actions.
- Gebruik route model binding, policies of expliciete ownership-checks. Een gebruiker mag uitsluitend eigen spots wijzigen of verwijderen.
- Gebruik Eloquent-relaties en eager loading om N+1-query’s te voorkomen. Zet querylogica niet in controllers.
- Bescherm mass assignment en schrijf geen ongefilterde requestdata naar modellen.
- Houd databasewijzigingen backward-compatible waar dat relevant is en leg defaults, indexes en foreign keys vast in migrations.
- Verberg provider- en storage-details achter services/configuratie. Lek geen geheime sleutels of ruwe externe responses naar de frontend.
- Houd responses via Inertia klein en voorspelbaar. Geef alleen props door die de pagina daadwerkelijk nodig heeft.

## Vue- en TypeScript-richtlijnen

- Gebruik Vue 3 met `<script setup lang="ts">`.
- Gebruik typed `defineProps`, `defineEmits` en `defineModel` waar passend. Vermijd `any`; maak of verbeter een type in `resources/js/types/`.
- Plaats feature-markup, formulieren, dialogs en andere herbruikbare schermdelen in partials. Houd een partial klein, leesbaar en verantwoordelijk voor één interactie of visueel blok.
- Laat partials communiceren via props, events en v-model. Verstop geen mutaties in impliciete globale state.
- Houd Inertia-pages verantwoordelijk voor compositie en navigatie; verplaats herbruikbare state naar een composable en feature-UI naar een partial.
- Gebruik `useForm` voor Inertia-formulieren en laat servervalidatiefouten zichtbaar terugkomen in de interface.
- Gebruik bestaande UI-primitives, layouts, composables en helpers voordat je een parallel patroon introduceert.
- Gebruik Tailwind volgens de bestaande stijl. Vermijd inline style-attributen en grote, onleesbare class strings waar een partial of helper de intentie duidelijker maakt.
- Zorg voor toegankelijke labels, focusgedrag, keyboardbediening, duidelijke foutmeldingen en passende `aria`-attributen.

## Leesbaarheid en structuur

- Schrijf code alsof een andere ontwikkelaar hem over zes maanden moet onderhouden.
- Kies namen die intentie beschrijven; vermijd namen als `data`, `item`, `handle` of `process` wanneer een domeinspecifieke naam mogelijk is.
- Houd functies kort en gebruik guard clauses voor ongeldige of lege situaties.
- Vermijd diepe nesting, verborgen side effects, magic numbers en duplicatie.
- Gebruik comments alleen voor de reden achter een niet-obvious keuze; laat comments niet herhalen wat de code al zegt.
- Houd importvolgorde en formattering in lijn met ESLint en Prettier. Gebruik bestaande aliasen zoals `@/` in TypeScript-imports.
- Maak wijzigingen lokaal en doelgericht. Herschrijf geen niet-gerelateerde code en behoud bestaand gedrag tenzij de opdracht dat expliciet verandert.

## Testen en kwaliteitscontrole

Schrijf of wijzig tests bij gedrag dat je verandert:

- unit-tests voor actions, queries, data-objecten en services;
- feature-tests voor routes, autorisatie, validatie en Inertia-responses;
- frontend-tests of minstens type/lint-controle voor gewijzigde Vue-logica.

Gebruik minimaal de relevante controles na een wijziging. Bij bredere wijzigingen:

```text
composer lint:check
composer types:check
npm run lint:check
npm run format:check
npm run types:check
php artisan test
```

Als een controle niet uitgevoerd kan worden, vermeld welke controle is overgeslagen en waarom. Beoordeel vóór het afronden ook autorisatie, lege staten, validatiefouten, loading states en de mobiele weergave.

## Werkwijze voor AI-bijdragen

1. Inspecteer eerst de relevante bestaande controller, action/query, types, page en partials.
2. Bepaal in welke laag de wijziging thuishoort en houd de dependency-richting van de architectuur aan.
3. Implementeer de kleinste complete wijziging met duidelijke namen en types.
4. Voeg tests of testdekking toe voor nieuw gedrag en voer relevante kwaliteitscontroles uit.
5. Rapporteer kort wat gewijzigd is, welke controles zijn uitgevoerd en eventuele resterende risico’s.

Vraag alleen om verduidelijking wanneer een keuze de architectuur, beveiliging, datamigratie of het gebruikersgedrag wezenlijk verandert. Maak anders een redelijke aanname en documenteer die in de uiteindelijke samenvatting.

# Field Reference

How form fields and submission values are shaped in API responses. Read this alongside [API endpoints](api-endpoints.md).

## Submission field values

A submission's `fields` map is keyed by field handle. Each entry has a consistent envelope:

```json
"email": {
  "label": "Email address",
  "handle": "email",
  "type": "Email",
  "value": "jane@example.com",
  "required": true
}
```

The `value` shape depends on the Formie field `type`:

| Field type | `value` shape |
|------------|---------------|
| SingleLineText, MultiLineText, Hidden, Email | String |
| Number, Calculations | Number (float), or `null` if non-numeric |
| Dropdown, Radio (single) | `{ "label": "...", "value": "..." }` |
| Checkboxes, multi Dropdown, Recipients | Array of `{ "label", "value" }` |
| Agree | Boolean |
| Date | ISO 8601 string (site timezone; the wall-clock time the user entered) |
| Name (single mode) | String |
| Name (multi mode) | `{ "fullName", "prefix", "prefixOption", "firstName", "middleName", "lastName" }` (populated keys only) |
| Phone (simple) | String (the number) |
| Phone (with country) | `{ "number", "country", "countryCode", "countryName", "international", "hasCountryCode" }` |
| Address | `{ "address1", "address2", "address3", "city", "state", "zip", "country", "countryOption" }` (populated keys only) |
| FileUpload | Array of `{ "filename", "url" }` |
| Entries, Categories, Tags, Products, Variants | Array of `{ "id", "title", "slug", "url" }` |
| Users | Array of `{ "id", "fullName", "email", "username" }` |
| Group | Object of inner-field handles → values |
| Repeater | Array of rows, each an object of inner-field handles → values |
| Table | Array of rows, each keyed by your column handles (typed per column) |
| Password | Always `null` — never returned, even hashed |
| Anything else | String, or JSON-encoded value as a fallback |

Structural/decorative fields (HTML, Heading, Section, Summary, Paragraph) carry no value and are omitted from the map entirely.

**Rating field** (LindemannRock Rating field plugin) entries add `minValue`, `maxValue`, and `ratingType` (`star`, `emoji`, or `nps`) alongside the standard envelope.

### Table cells

Each table cell is cast by its column type: `number` → float, `date`/`time` → ISO 8601, `color` → `#rrggbb`, `checkbox` → boolean, everything else → string. Heading columns are decorative and omitted.

## Form field metadata

On the form **detail** endpoints, each entry in the form's `fields` array describes the field's configuration:

```json
{
  "handle": "email",
  "label": "Email address",
  "type": "Email",
  "required": true,
  "instructions": "We'll only use this to reply.",
  "errorMessage": null,
  "appearance": { "visibility": "visible", "labelPosition": "Above" },
  "settings": { /* field-type-specific settings */ }
}
```

- `appearance` holds UI-rendering hints (visibility, label/instructions position, layout).
- `settings` holds data-behaviour settings specific to the field type.
- `advanced` (when present) holds CSS classes and container/input attributes.
- `conditions` (when the field opts in) is a flattened `{ enabled, showRule, conditionRule, rules }`.
- `subFields` (Name multi, Date, Address, Group, Repeater) recursively describes each nested field, each independently configurable.

### Field metadata units

A few setting values where the unit isn't obvious:

| Field type | Setting | Unit |
|------------|---------|------|
| FileUpload | `sizeLimit`, `sizeMinLimit` | Megabytes (MB) |
| FileUpload | `limitFiles` | Number of files |
| Signature | `penWeight` | Pixels |
| SingleLineText / MultiLineText | `min`, `max` | Characters or words — see the companion `minType` / `maxType` |
| Date | `minDateOffsetNumber`, `maxDateOffsetNumber` | See `minDateOffsetType` / `maxDateOffsetType` (`days`, `weeks`, `months`, `years`) |
| Date | `minYearRange`, `maxYearRange` | Years offset from the current year (e.g. `-100` = 100 years ago) |

Deterministic enumerations (Date sub-dropdown options, the Address country list) are omitted to keep responses lean — build them from standard data sources client-side.

## Notes

- All datetimes (submission timestamps, Date field values, table date cells) are **ISO 8601 in the site timezone**.
- A sparse `fields=` query (see [API endpoints](api-endpoints.md)) limits the submission `fields` map to the handles you request, and skips value resolution for the rest.

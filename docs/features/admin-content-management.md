# Admin Content Management

App admins create, edit, and delete the shared content catalog: equipment, categories, and exercises.

## Current Behavior

1. A user with the `Admin` role sees an **Admin** link in the desktop and mobile navigation. Non-admins never see it.
2. Admin opens **Admin/Index** and picks a section: Equipment, Categories, or Exercises.
3. Each section page lists existing records with **Edit** and **Delete** actions and a **New** button.
4. Creating or editing opens a modal form. Text fields are captured per locale — English (required) and Russian (optional).
5. On save the record is persisted through the REST API and the list refreshes.
6. Deleting prompts a confirmation dialog before removing the record.

## Business Rules

- All admin actions require the authenticated user to have the `Admin` role. Enforced by the `admin` route middleware and by a per-model policy (`EquipmentPolicy`, `CategoryPolicy`, `ExercisePolicy`); non-admins receive `403`.
- Content is bilingual. Each translatable field accepts an English value (required) and a Russian value (optional). A blank Russian value is removed so reads fall back to English.
- **Equipment** carries a `difficulty_unit` (kilograms, pounds, plates, none) and a translatable `name`. Equipment referenced by any exercise cannot be deleted; the API returns `409`.
- **Category** carries a translatable `name`. Deleting a category detaches it from its exercises.
- **Exercise** carries `equipment_id`, `effort_type` (repetitions, duration), `rest_time_seconds`, a set of categories, and translatable `name` + `description`. Category membership is synced on every save.
- Write operations run in a database transaction; exercise category sync and translation writes commit together.

## Known Limitations

- Programs and workout templates are not yet manageable here (planned as later phases).
- No search, filter, sort, or pagination on the admin list pages.
- No image or media management for exercises.
- Admin editing covers English and Russian only, matching the app's supported locales.
- No audit log of admin changes.

## Surface Area

- Pages: `Admin/Index`, `Admin/EquipmentIndex`, `Admin/CategoryIndex`, `Admin/ExerciseIndex`
- Web route names: `admin.index`, `admin.equipment`, `admin.categories`, `admin.exercises`
- API route names: `api.v1.admin.equipment.*`, `api.v1.admin.categories.*`, `api.v1.admin.exercises.*` (`index`, `store`, `update`, `destroy`)
- Full reference: [Pages & Routes](../pages-and-routes.md)

## Data Notes

Translatable text is not stored on the base tables; it lives in the polymorphic `translations` table. `HasTranslations::createWithTranslations()` seeds new records and `HasTranslations::updateTranslations()` applies edits (removing blank values for fallback). See [Architecture](../architecture.md) for the translation strategy.

## Related

- [Programs](programs.md)
- [Architecture](../architecture.md)
- [Product Overview](../product.md)

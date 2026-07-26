# Admin Content Management

App admins create, edit, and delete the shared content catalog: equipment, categories, exercises, workout templates, and programs.

## Current Behavior

1. A user with the `Admin` role sees an **Admin** link in the desktop and mobile navigation. Non-admins never see it.
2. Admin opens **Admin/Index** and picks a section: Equipment, Categories, Exercises, Workout templates, or Programs.
3. Each section page lists existing records with **Edit** and **Delete** actions and a **New** button.
4. Creating or editing opens a modal form. Text fields are captured per locale — English (required) and Russian (optional).
5. The workout-template form is a nested builder: add activities (each picks an exercise), and add sets under each activity (effort value plus a difficulty field whose type follows the exercise's equipment — a number for weights, a 1–5 picker for heart-rate zones, nothing for bodyweight).
6. The program form edits translations plus a weekly schedule: each row assigns a workout template to a weekday (the same template may be scheduled on more than one weekday).
7. On save the record is persisted through the REST API and the list refreshes.
8. Deleting prompts a confirmation dialog before removing the record.

## Business Rules

- All admin actions require the authenticated user to have the `Admin` role. Enforced by the `admin` route middleware and by a per-model policy (`EquipmentPolicy`, `CategoryPolicy`, `ExercisePolicy`, `WorkoutTemplatePolicy`, `ProgramPolicy`); non-admins receive `403`.
- Content is bilingual. Each translatable field accepts an English value (required) and a Russian value (optional). A blank Russian value is removed so reads fall back to English.
- **Equipment** carries a `difficulty_unit` (kilograms, pounds, plates, none) and a translatable `name`. Equipment referenced by any exercise cannot be deleted; the API returns `409`.
- **Category** carries a translatable `name`. Deleting a category detaches it from its exercises.
- **Exercise** carries `equipment_id`, `effort_type` (repetitions, duration), optional `rest_time_seconds`, a set of categories, and translatable `name` + `description`. Category membership is synced on every save.
- **Workout template** carries translatable `name` + `description` and an ordered list of activities, each an exercise with one or more sets (`effort_value`, optional `difficulty_value`). At least one activity, and at least one set per activity, are required. A set's `difficulty_value` is validated against the exercise's difficulty unit — heart-rate-zone exercises accept 1–5 (via `HeartRateZoneWithinRange`). Updating a template replaces its activities and sets wholesale.
- **Program** carries translatable `name` + `description` and a set of weekday assignments (`{workout_template_id, weekday}`), stored on the `program_workout_template` pivot. Assignments are optional; the same template may be scheduled on multiple weekdays. Updating a program replaces its assignments wholesale. Enrollment (`program_user`) is unaffected by admin edits.
- Write operations run in a database transaction; exercise category sync, template activities/sets, program assignments, and translation writes commit together.

## Known Limitations

- Programs support only a single weekly schedule — no multi-week periodization.
- No search, filter, sort, or pagination on the admin list pages.
- No image or media management for exercises.
- Admin editing covers English and Russian only, matching the app's supported locales.
- No audit log of admin changes.

## Surface Area

- Pages: `Admin/Index`, `Admin/EquipmentIndex`, `Admin/CategoryIndex`, `Admin/ExerciseIndex`, `Admin/WorkoutTemplateIndex`, `Admin/ProgramIndex`
- Web route names: `admin.index`, `admin.equipment`, `admin.categories`, `admin.exercises`, `admin.workout-templates`, `admin.programs`
- API route names: `api.v1.admin.equipment.*`, `api.v1.admin.categories.*`, `api.v1.admin.exercises.*` (`index`, `store`, `update`, `destroy`); `api.v1.admin.workout-templates.*` and `api.v1.admin.programs.*` (`index`, `show`, `store`, `update`, `destroy`)
- Full reference: [Pages & Routes](../pages-and-routes.md)

## Data Notes

Translatable text is not stored on the base tables; it lives in the polymorphic `translations` table. `HasTranslations::createWithTranslations()` seeds new records and `HasTranslations::updateTranslations()` applies edits (removing blank values for fallback). See [Architecture](../architecture.md) for the translation strategy.

## Related

- [Programs](programs.md)
- [Architecture](../architecture.md)
- [Product Overview](../product.md)

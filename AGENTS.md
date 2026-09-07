## Scope and safety

- Treat the user's current request as the source of truth.
- Before editing, inspect the relevant code, routes, migrations, tests, and project state. Do not guess about existing behavior.
- Keep each change narrowly focused on the requested feature or fix. Avoid unrelated refactors, formatting churn, dependency upgrades, or speculative improvements.
- Preserve existing user changes. Do not reset, discard, overwrite, or revert work that was not created for the current task.
- Do not delete files, change data destructively, install packages, alter environment/configuration, commit, push, deploy, or contact external services unless the user explicitly requests it.
- Ask before taking an action that could materially change the scope, data, API, architecture, or user-visible behavior.
- Prefer reversible operations and small, reviewable patches.

## Working style

- For implementation requests, briefly state the understanding, assumptions, and intended scope before making changes.
- If a choice could substantially affect the result, ask a focused question. For routine details, make the smallest reasonable assumption and state it.
- Do not stop at a plan when the user has authorized implementation; carry the task through to a reviewable result.
- Do not use subagents unless delegation is explicitly requested or clearly justified by the user.
- Explain what changed in plain language and identify any limitations or follow-up work.

## Repository conventions

- This is a Laravel application with an Inertia/React frontend. Follow the existing structure, naming, formatting, and patterns before introducing new ones.
- Prefer framework conventions and existing components/services over adding abstractions.
- Keep business rules in the appropriate backend layer and keep controllers and UI components focused.
- Treat migrations and schema changes as consequential: describe their effect and ask for confirmation before creating them unless the user explicitly included them in the request.
- Do not expose secrets from `.env` or other sensitive files.

## Verification

- Run checks appropriate to the change, such as targeted tests, `php artisan test`, route/config checks, or the frontend build.
- Report exactly which checks were run and whether they passed.
- If a check fails, show the relevant failure, investigate the cause, and do not claim completion until the issue is resolved or clearly reported as blocked.
- Do not add tests that merely mirror trivial implementation. Add meaningful tests for business rules, authorization, validation, persistence, and regressions.

## Review and Git

- Before finishing, inspect the diff and summarize every changed or added file.
- Never create a commit unless explicitly asked.
- If the working tree contains unrelated changes, leave them untouched and call them out when relevant.
- A task is complete only when the requested behavior is implemented, relevant verification is done, and remaining risks are communicated.


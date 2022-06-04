## ~~Instructions for PR author (to remove before saving)~~

~~PR author should:~~

 * ~~name it's PR with the name of the issue it solved.~~
 * ~~add in the PR name if the code targets backend or frontend with relevant label~~
 * ~~add relevant description to help reviewer understand the work he has done~~
 * ~~whenever possible, make the smallest changes possible to help reviewer work (ie. short branch)~~
 * ~~find someone to review the branch and is responsible for it to be reviewed by someone else~~

~~This is all in the spirit of reviewing and merging branch faster.~~

## Code review

### Instructions for reviewer

Reviewer should have read the [quality assurance guide](/doc/development/quality-assurance/quality-assurance.asc)
before doing a review.

### Check list

Following points must be confirmed and checked explicitly by code reviewer :

 * [ ] Does it conform to [coding conventions](/doc/development/style-guide/style-guide.asc) (check phpmd output)?
 * [ ] Is there unit tests asserting the code is working well (does the coverage is lower than main branch)?
 * [ ] All critical comments (:bomb:) are fixed?

### Features added or errors corrected


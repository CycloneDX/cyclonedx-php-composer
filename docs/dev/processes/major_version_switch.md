# How to plan/do a MAJOR release

The following steps must be done,
when a MAJOR release `n+1.0.0` is planned or happens:

1. Update the branch alias in [`composer.json`](../../../composer.json)
   at `extra.branch-alias.dev-master`
   to `n+1.x-dev`
   on `master` branch.
1. Create a branch `n.x` from `master`.
1. Optional:
   Remove the `extra.branch-alias`
   in [`composer.json`](../../../composer.json)
   on `n.x` branch.
1. Add branch `n.x` to "branch protection"
   and copy the settings from currently protected `master` to it.
1. Do the usual dev-/release-process in `master` branch.

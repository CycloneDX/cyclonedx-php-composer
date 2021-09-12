# Bom Dependency DataModel

CDX >= 1.2 allowed to reflect on dependencies.  

See https://cyclonedx.org/use-cases/#dependency-graph
So from the examples JSON and XML representations are clear.

Unfortunately the spec was designed to put dependencies into dependencies,
which might allow cyclic data structures.

Challenges:
* How to store dependency references without causing memory leaks(by cyclic references)?  
  How to store dependencies in a way that prevents recursions/cycles?  
  How to detect/prevent recursions when traversing to render the XML/JSON?
* Where to store the data?  
  Have actual dependencies inside an actual instance of `Component` or have the data storage extra?
* How to keep data consistent and `bom-ref` markers unique in the SBoB result XML/JSON?

## Store without memory leaks:

### Option A

Rule: prevent nesting of dependencies.

Can be achieved by storing a ref in form of an extra data type, called `BomRef` inside the dependency tree.  
A component could simply hold that same `BomRef` to flag it as the target of a dependency.
 
### Option B

Rule: prevent nesting of dependencies.

Can be achieved by probing the child-tree of an item before adding it

When adding a new dependency called `newChild` to `parent`, follow all nested dependencies of `newChild` per branch until
a) there is no child (reached a leaf) --> adding of `newChild` possible  
b) an already traversed item is found --> cycle found; adding of `newChild` prohibited  
To fully cover possible cycles, the first visited item is the `parent` itself.

### Option C

Rule: don't give a ***

Whatever created the cycle should have had it prevented in the first place.

## How to store the dependencies

### Option A

There could be a new property to a `Bom` object, that holds the dependency tree.

### Option B 

There could be a new property to a `Component` object, that holds the dependency tree.

## keeping data consistent

Options depend on the DataStorage decision.

An option that is always available: filter/check on render-time.  
So when serializing the models to XML/JSON a check could be made, dependencies that are unknown components could be skipped.

## DECISION

* A new data type is introduced, to be used as a marker for "ref".
  This marker can be linked between components, instead of linking the components directly.
  With this method there will be no memory leaks.
* Components get to know their "ref".  
  There will be no logic in place that keeps "ref" markers globally unique on runtime.  
  There may be logic in place that keeps "ref" markers globally unique when rendering.
* Components hold information about their first-level-dependencies; no extra data model for dependencies.  
  First-level only: This will prevent nesting but still having a graph on logic-level.
* Cycles are prevented on render-time, but not on logic-level:
  This allowes to make dependency-cycles visible.
  * For each component: render the first level dependencies only.  
    This assumes, that the component of the `metadata` is also rendered.
  * Components that don't have any dependency are rendered, still.
  * Skip dependencies to unknown components.

### implementation details

#### design

* New data tye `BomRef`
  * Has a private property `value` of nullable string type. `value` is null/undefined per default.
  * Property `value` which can be modified via getter/setter/constructor.  
  * When `value` is unset, and the getter is called, then `value` is set to a unique id.
  * if `BomRef` is castable to string, then the string is equal to the return of the `value`-getter.
* New data tye `BomRefRepository`
  * is a collection of `BomRef`
* components have a new property `bomRef` of type `BomRef`.
  * `bomRef` is created on construction and cannot be changed/set manually.
  * There is a getter for `bomRef`
* components have a new private property `dependencies` of type `BomRefRepository`.
  * property is nullable, default null
  * property can be accessed via getter/setter

#### rendering

Rendering is skipped, if the appllied spec does not support it (spec version < 1.2)

When rendering to XML/JSON some cleanup needs to be done:
Detect dependencies that point to `BomRef` that are unknown 
in context of `metadata.component.bomRef` and `components.*.bomRef`. 
As long as nested/sub-components are not supported, the look-up is just a 1-level tree search.  
The found unknown references must not be rendered as a dependency.

`BomRefs` are objects, so they are unique in memory, but their string representation might not be unique.
> Uniqueness *must* be enforced within all elements and children of the root-level bom element.  

Before rendering, a list of all known `BomRef` *must* be collected. see if their values are globally unique.
If a duplicate is found: use another globally unique value instead. This can be achieved by modification to the `BomRef.value` before rendering and resetting it after rendering.

All components and the metadata component *should* be rendered.
> Components that do not have their own dependencies MUST be declared as empty elements within the graph. Components that are not represented in the dependency graph MAY have unknown dependencies. It is RECOMMENDED that implementations assume this to be opaque and not an indicator of a component being dependency-free.

#### composer specifics

* need to check how composer's alias-packages could be handled.

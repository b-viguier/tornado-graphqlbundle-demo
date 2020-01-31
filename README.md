# tornado-graphqlbundle-demo

[GraphQLBundleDemo](https://github.com/overblog/GraphQLBundleDemo) running with Tornado.

This demo is based on a previous [Tornado workshop](https://github.com/b-viguier/tornado-workshop),
the goal is to expose a Rest-ish API through this GraphQL bundle.

## Install

```
composer install
bin/console c:w
```

## Start server

```
bin/console server:run
```

## Endpoints

* [GraphQL](http://127.0.0.1:8000/graphql/quickstart)
* [GraphiQL](http://127.0.0.1:8000/graphiql/quickstart)

## Your turn

* Add missing entities (words, letters, characters).
* Add computed words, sentences and full text.
* Improve performances with cache, paginator…
* Delete the TornadoGqlAdapter, and write it yourself!

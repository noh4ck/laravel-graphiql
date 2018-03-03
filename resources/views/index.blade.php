<!--
 *  Copyright (c) 2015, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the license found in the
 *  LICENSE file in the root directory of this source tree.
 *
-->
<!DOCTYPE html>
<html>
<head>
  <style>
    body {
      height: 100%;
      margin: 0;
      width: 100%;
      overflow: hidden;
    }
    #graphiql {
      height: 100vh;
    }
    .jwt-token{
      padding:10px;
      background:#f7f7f7;
      font-family: system, -apple-system, 'San Francisco', '.SFNSDisplay-Regular', 'Segoe UI', Segoe, 'Segoe WP', 'Helvetica Neue', helvetica, 'Lucida Grande', arial, sans-serif;
    }
    .jwt-token input {
      display: inline-block;
      width: 400px;
      padding: 5px;
      border:1px solid #d0d0d0;
      margin-left: 5px;
      color: #777777;
      border-radius: 3px;
    }

    .jwt-token button#remove-token{
      background: linear-gradient(#f9f9f9,#ececec);
      border-radius: 3px;
      box-shadow: inset 0 0 0 1px rgba(0,0,0,.2), 0 1px 0 rgba(255,255,255,.7), inset 0 1px #fff;
      color: #555;
      border: 0px;
      margin: 0 5px;
      padding: 6px 11px 6px;
      cursor:pointer;
    }
  </style>

  <link rel="stylesheet" href="{{config('graphiql.paths.assets_public')}}/graphiql.css" />

  {{--<script src="https://cdn.jsdelivr.net/gh/github/fetch@0.9.0/fetch.min.js"></script>--}}
  <script>window.fetch || document.write('<script src="{{config('graphiql.paths.assets_public')}}/vendor/fetch.min.js">\x3C/script>')</script>
  {{--<script crossorigin src="https://unpkg.com/react@16/umd/react.production.min.js"></script>--}}
  <script>window.React || document.write('<script src="{{config('graphiql.paths.assets_public')}}/vendor/react.min.js">\x3C/script>')</script>
  {{--<script crossorigin src="https://unpkg.com/react-dom@16/umd/react-dom.production.min.js"></script>--}}
  <script>window.ReactDOM || document.write('<script src="{{config('graphiql.paths.assets_public')}}/vendor/react-dom.min.js">\x3C/script>')</script>
  {{--<script src="//unpkg.com/subscriptions-transport-ws@0.5.4/browser/client.js"></script>--}}
  <script>window.SubscriptionsTransportWs || document.write('<script src="{{config('graphiql.paths.assets_public')}}/vendor/subscriptions.js">\x3C/script>')</script>
  {{--<script src="//unpkg.com/graphiql-subscriptions-fetcher@0.0.2/browser/client.js"></script>--}}
  <script>window.GraphiQLSubscriptionsFetcher || document.write('<script src="{{config('graphiql.paths.assets_public')}}/vendor/graphiql-subscriptions.js">\x3C/script>')</script>

  <script src="{{config('graphiql.paths.assets_public')}}/graphiql.js"></script>

</head>
<body>
<div class="jwt-token">
  <label>Token</label>
  <input id="jwt-token" placeholder="Paste token (without Bearer)">
  <button id="remove-token">✖</button>
</div>
<div id="graphiql">Loading...</div>
<script>

    /**
     * This GraphiQL example illustrates how to use some of GraphiQL's props
     * in order to enable reading and updating the URL parameters, making
     * link sharing of queries a little bit easier.
     *
     * This is only one example of this kind of feature, GraphiQL exposes
     * various React params to enable interesting integrations.
     */

        // Parse the search string to get url parameters.
    var search = window.location.search;
    var parameters = {};
    search.substr(1).split('&').forEach(function (entry) {
        var eq = entry.indexOf('=');
        if (eq >= 0) {
            parameters[decodeURIComponent(entry.slice(0, eq))] =
                decodeURIComponent(entry.slice(eq + 1));
        }
    });

    document.getElementById('jwt-token').value = localStorage.getItem('graphiql:jwtToken');
    var remove_token = document.getElementById('remove-token');
    remove_token.onclick = function(){
        localStorage.removeItem('graphiql:jwtToken');
        document.getElementById('jwt-token').value = '';
    }

    // if variables was provided, try to format it.
    if (parameters.variables) {
        try {
            parameters.variables =
                JSON.stringify(JSON.parse(parameters.variables), null, 2);
        } catch (e) {
            // Do nothing, we want to display the invalid JSON as a string, rather
            // than present an error.
        }
    }

    // When the query and variables string is edited, update the URL bar so
    // that it can be easily shared
    function onEditQuery(newQuery) {
        parameters.query = newQuery;
        updateURL();
    }

    function onEditVariables(newVariables) {
        parameters.variables = newVariables;
        updateURL();
    }

    function onEditOperationName(newOperationName) {
        parameters.operationName = newOperationName;
        updateURL();
    }

    function updateURL() {
        var newSearch = '?' + Object.keys(parameters).filter(function (key) {
            return Boolean(parameters[key]);
        }).map(function (key) {
            return encodeURIComponent(key) + '=' +
                encodeURIComponent(parameters[key]);
        }).join('&');
        history.replaceState(null, null, newSearch);
    }

    // Defines a GraphQL fetcher using the fetch API.
    function graphQLFetcher(graphQLParams) {

        const jwtToken = document.getElementById('jwt-token').value;
        localStorage.setItem('graphiql:jwtToken', jwtToken);

        //return fetch("http://localhost:8000/graphql", {
        return fetch("{{url(config('graphiql.routes.graphql'))}}", {
            method: 'post',
            headers: {
              @forelse(config('graphiql.headers') as $key => $value)
              '{{ $key }}': '{{ $value }}',
              @empty
              'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': jwtToken ? 'Bearer '+jwtToken : null
              @endforelse
            },
            body: JSON.stringify(graphQLParams),
            credentials: 'include',
        }).then(function (response) {
            return response.text();
        }).then(function (responseBody) {
            try {
                return JSON.parse(responseBody);
            } catch (error) {
                return responseBody;
            }
        });
    }

    var subscriptionsClient = new window.SubscriptionsTransportWs.SubscriptionClient("{{config('graphiql.webSocketEndPoint')}}" , {
        reconnect: true
    });

    graphQLFetcher = window.GraphiQLSubscriptionsFetcher.graphQLFetcher(subscriptionsClient, graphQLFetcher);

    // Render <GraphiQL /> into the body.
    ReactDOM.render(
        React.createElement(GraphiQL, {
            fetcher: graphQLFetcher,
            query: parameters.query,
            variables: parameters.variables,
            operationName: parameters.operationName,
            onEditQuery: onEditQuery,
            onEditVariables: onEditVariables,
            onEditOperationName: onEditOperationName
        }),
        document.getElementById('graphiql')
    );
</script>
</body>
</html>

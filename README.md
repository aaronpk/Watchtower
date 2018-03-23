Watchtower
==========

Watchtower is a minimal API that watches web pages for changes and notifies subscribers. Its API is similar to [WebSub](https://www.w3.org/TR/websub/), as well as [Superfeedr subscriptions](https://documentation.superfeedr.com/subscribers.html).

For HTML pages, Watchtower compares the text content with all tags removed in order to determine whether a page has changed. This prevents things like CSRF tokens from triggering a change event and redelivery of the page. For all other content types, the raw content is used to compare changes. If a page has changed more than 2% then it will be delivered to subscribers.


API
---

Every API request requires authenticating with an application API key included as a Bearer Token.

```
POST / HTTP/1.1
Authorization: Bearer xxxxxxxxxx
```

### Subscribing

To subscribe to changes of a URL, send a POST request to the root URL with the following parameters

`POST https://watchtower.example/`

* `hub.mode` = `subscribe`
* `hub.topic` the URL that you want to watch
* `hub.callback` the subscriber's URL to be notified of changes

Unlike WebSub, Watchtower does not make an initial verification request to check the callback URL. It assumes it's valid since the API request must also include the API key.

Watchtower will create the subscription, then deliver the current contents of the URL to the subscriber. This happens asynchronously so it may take a few seconds after the API request.

### Unsubscribing

To unsubscribe, send a POST request with the following parameters

`POST https://watchtower.example/`

* `hub.mode` = `unsubscribe`
* `hub.topic` the URL that you want to watch
* `hub.callback` the subscriber's URL to be notified of changes

The subscription will be deactivated immediately.


### Web Hooks

When Watchtower delivers a notification to the subscriber, it makes an HTTP POST with a content type header matching the content type of the topic, and the body of the POST is the full contents of the URL. It also includes an `Authorization` header with the API key, so that you can verify the authenticity of the API request.


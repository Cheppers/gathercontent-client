# GatherContent REST client

### Initialization

#### Credentials

You will need:

- your e-mail address to log into GatherContent
- your [API key](https://docs.gathercontent.com/reference#authentication) from GatherContent

```php
$email = 'YOUR_GATHERCONTENT_EMAIL';
$apiKey = 'YOUR_GATHERCONTENT_API_KEY';
```

#### Client

You will need Guzzle (see composer.json dependency)

To create the GatherContentClient simply pass in the Guzzle client in the constructor.

```
$client = new \GuzzleHttp\Client();
$gc = new GatherContentClient($client);
$gc
  ->setEmail($email)
  ->setApiKey($apiKey);
```

### Methods

#### meGet

Access GatherContent's /me endpoint.

Return PHP array;

```
$r = $gc->meGet();
```


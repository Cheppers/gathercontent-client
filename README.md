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

## Methods

### Me

#### meGet

Endpoint: **GET: /me**

Returns the logged in user.

Return type: ```User```

```
$user = $gc->meGet();
```

### Accounts

#### accountsGet

Endpoint: **GET: /accounts**

Returns all the accounts associated with the logged in user.

Return type: ```Account[]```

```
$ąccounts = $gc->accountsGet();
```

#### accountGet

Endpoint: **GET: /accounts/:account_id**

Returns a specific account of the logged in user.

Parameters:

- ```int $accountId```: the ID of the account to retrieve

Return type: ```Account```

```
$ąccount = $gc->accountGet($accountId);
```

### Projects

#### projectsGet

Endpoint: **GET: /projects**

Returns all the projects associated with the given account id.
 
Paramteres:

- ```int $accountId```: the ID of the account

Return type: ```Project[]```

```
$projects = $gc->projectsGet($accountId);
```

#### projectGet

Endpoint: **GET: /projects/:project_id**

Returns a specific project.

Paramteres:

- ```int $projectId```: the ID of the project to retrieve

Return type: ```Project```

```
$project = $gc->projectGet($projectId);
```

#### projectsPost

Endpoint: **POST: /projects**

Create a new project for a specific account.
Returns the id of the newly created project.

Parameters:

- ```int $accountId```: the ID of the account

- ```string $projectName```: the name of the new project

- ```string $projectType```: the project type
    - the available options can be found in ```GatherContentClientInterface```
        - ```PROJECT_TYPE_WEBSITE_BUILDING```
        - ```PROJECT_TYPE_ONGOING_WEBSITE_CONTENT```
        - ```PROJECT_TYPE_MARKETING_EDITORIAL_CONTENT```
        - ```PROJECT_TYPE_EMAIL_MARKETING_CONTENT```
        - ```PROJECT_TYPE_OTHER```

Return type: ```int```

```
$projectId = $gc->projectsPost(
    $accountId,
    $projectName,
    GatherContentClientInterface::PROJECT_TYPE_ONGOING_WEBSITE_CONTENT
);
```

#### projectStatusesGet

Endpoint: **GET: /projects/:project_id/statuses**

Get all the statuses associated with a given project.

Parameters:

- ```int $projectId```: the ID of the project

Return type: ```Status[]```

```
$statuses = $gc->projectStatusesGet($projectId);
```

#### projectStatusGet

Endpoint: **GET: /projects/:project_id/statuses/:status_id**

Returns a project's given status.

Parameters:

- ```int $projectId```: the ID of the project

- ```int $statusId```: the ID of the status

Return type: ```Status```

```
$status = $gc->projectStatusGet($projectId, $statusId);
```

### Items

#### itemsGet

Endpoint: **GET: /items**

Return all the items of a project.

Parameters:

- ```int $projectId```: the ID of the project

Return type: ```Item[]```

```
$items = $gc->itemsGet($projectId);
```

#### itemGet

Endpoint: **GET: /items/:item_id**

Return a specific item.

Parameters:

- ```int $itemId```: the ID of the item

Return type: ```Item```

```
$item = $gc->itemGet($itemId);
```

#### itemsPost

Endpoint: **POST: /items**

Create a new item for a specific project.
Returns the id of the newly created item.

Parameters:

- ```int $projectId```: the ID of the project

- ```string $name```: the name of the new item

- ```int $parentId```: the ID of the parent item

- ```int $templateId```: the ID of the template

- ```array $config```: the config array for the new item

Return type: ```int```

```
$itemId = $gc->itemsPost(
    $projectId,
    $name,
    $parentId,
    $templateId,
    $config
);
```

The ```$config``` array should look like this:

```
$tab = new Tab();
$tab->label = 'Test tab';
$tab->id = 'test_tab';
$tab->hidden = false;
```

It's first dimension must be a ```Tab``` object, with a unique tab ID.

```
$config[$tab->id] = $tab;
```

The tabs contain the field elements.

```
$text = new ElementText();
$text->label = 'Test text';
$text->id = 'test_text';
$text->type = 'text';
$text->limitType = 'words';
$text->limit = 1000;
$text->value = 'Test value';
```

And simply put them together like this:

```
$config[$tab->id]->elements[$text->id] = $text;
```

#### itemSavePost

Endpoint: **POST: /items/:item_id/save**

Edit an existing item.

Parameters:

- ```int $itemId```: the ID of the item

- ```array $config```: the config array for the new item

Return type: ```void```

```
$gc->itemSavePost(
    $itemId,
    $config
);
```

The config is the same as at the ```itemsPost``` method.

#### itemApplyTemplatePost

Endpoint: **POST: /items/:item_id/apply_template**

Edit an existing item.

Parameters:

- ```int $itemId```: the ID of the item

- ```int $templateId```: the ID of the template

Return type: ```void```

```
$gc->itemApplyTemplatePost(
    $itemId,
    $templateId
);
```

#### itemChooseStatusPost

Endpoint: **POST: /items/:item_id/choose_status**

Edit an existing item.

Parameters:

- ```int $itemId```: the ID of the item

- ```int $statusId```: the ID of the status

Return type: ```void```

```
$gc->itemChooseStatusPost(
    $itemId,
    $templateId
);
```

#### itemFilesGet

Endpoint: **GET: /items/:item_id/files**

Return all the files of an item.

Parameters:

- ```int $itemId```: the ID of the item

Return type: ```File[]```

```
$files = $gc->itemFilesGet(
    $itemId
);
```

### Templates

#### templatesGet

Endpoint: **GET: /templates**

Return all the templates of a project.

Parameters:

- ```int $projectId```: the ID of the project

Return type: ```Template[]```

```
$templates = $gc->templatesGet($projectId);
```

#### templateGet

Endpoint: **GET: /templates/:template_id**

Return a specific template.

Parameters:

- ```int $templateId```: the ID of the project

Return type: ```Template```

```
$template = $gc->templateGet($templateId);
```
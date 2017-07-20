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

Return the logged in user.

Return type: ```User```

```
$user = $gc->meGet();
```

### Accounts

#### accountsGet

Endpoint: **GET: /accounts**

Return all the accounts associated with the logged in user.

Return type: ```Account[]```

```
$ąccounts = $gc->accountsGet();
```

#### accountGet

Endpoint: **GET: /accounts/:account_id**

Return a specific account of the logged in user.

Parameters:

- ```int $accountId```: the ID of the account to retrieve

Return type: ```Account```

```
$ąccount = $gc->accountGet($myAccountId);
```

### Projects

#### projectsGet

Endpoint: **GET: /projects**

Return all the projects associated with the given account id.
 
Paramteres:

- ```int $accountId```: the ID of the account

Return type: ```Project[]```

```
$projects = $gc->projectsGet($myAccountId);
```

#### projectGet

Endpoint: **GET: /projects/:project_id**

Return a specific project.

Paramteres:

- ```int $projectId```: the ID of the project to retrieve

Return type: ```Project```

```
$project = $gc->projectGet($myProjectId);
```

#### projectsPost

Endpoint: **POST: /projects**

Create a new project for a specific account.
Return the id of the newly created project.

Paramters:

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
    $myAccountId,
    $myNewProjectName,
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
$statuses = $gc->projectStatusesGet($myProjectId);
```

#### projectStatusGet

Endpoint: **GET: /projects/:project_id/statuses/:status_id**

Return a project's given status.

Parameters:

- ```int $projectId```: the ID of the project

- ```int $statusId```: the ID of the status

Return type: ```Status```

```
$status = $gc->projectStatusGet($myProjectId, $myStatusId);
```

### Items

#### itemsGet

Endpoint: **GET: /items**

Return all the items of a project.

Parameters:

- ```int $projectId```: the ID of the project

Return type: ```Item[]```

```
$items = $gc->itemsGet($myProjectId);
```

#### itemGet

Endpoint: **GET: /items/:item_id**

Return a specific item.

Parameters:

- ```int $itemId```: the ID of the item

Return type: ```Item```

```
$item = $gc->itemGet($myItemId);
```

#### itemsPost

Endpoint: **POST: /items**

#### itemSavePost

Endpoint: **POST: /items/:item_id/save**

#### itemApplyTemplatePost

Endpoint: **POST: /items/:item_id/apply_template**

#### itemChooseStatusPost

Endpoint: **POST: /items/:item_id/choose_status**

#### itemFilesGet

Endpoint: **GET: /items/:item_id/files**

### Templates

#### templatesGet

Endpoint: **GET: /templates**

#### templateGet

Endpoint: **GET: /templates/:template_id**
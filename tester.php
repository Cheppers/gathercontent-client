<?php

use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientInterface;

require_once __DIR__ . '/vendor/autoload.php';

$email = getenv('GATHERCONTENT_EMAIL');
$apiKey = getenv('GATHERCONTENT_API_KEY');
$cheppersAccountId = 25245;
$myProjectId = 86701;
$myStatusId = 432448;
$myItemIdFiles = 3194630;
$myItemIdOptions = 3309690;
$myItemIdTemplateApply = 3227765;
$myItemIdTemplateChooseStatus = 3227765;
$myFileId = 1620212;
$myTemplateIdChoice = 442692;
$myTemplateIdFile = 423189;
$myTemplateIdItem = 450969;

// Archived.
//$myItemId = 4846725;

$client = new \GuzzleHttp\Client();
$gc = new GatherContentClient($client);
$gc
    ->setEmail($email)
    ->setApiKey($apiKey);

//$r = $gc->meGet();
//$r = $gc->accountsGet();
//$r = $gc->accountGet($cheppersAccountId);
//$r = $gc->projectsGet($cheppersAccountId);
//$r = $gc->projectGet($myProjectId);
//$r = $gc->projectsPost(
//    $cheppersAccountId,
//    sprintf('Andor - REST - %s', date('H:i:s')),
//    GatherContentClientInterface::PROJECT_TYPE_ONGOING_WEBSITE_CONTENT
//);
//$r = $gc->projectStatusesGet($myProjectId);
$r = $gc->projectStatusGet($myProjectId, $myStatusId);
//$r = $gc->itemsGet($myProjectId);
//$r = $gc->itemGet($myItemIdFiles);
//$r = $gc->itemFilesGet($myItemIdFiles);
//$r = $gc->templatesGet($myProjectId);
//$r = $gc->templateGet($myTemplateIdFile);
$r = $gc->itemApplyTemplatePost($myItemIdTemplateApply, $myTemplateIdItem);
//$r = $gc->itemChooseStatusPost($myItemIdTemplateChooseStatus, $myStatusId);

echo print_r($gc->getResponse()->getHeaders(), true), PHP_EOL;
echo json_encode(\GuzzleHttp\json_decode($gc->getResponse()->getBody(), true), JSON_PRETTY_PRINT), PHP_EOL;
echo json_encode($r, JSON_PRETTY_PRINT), PHP_EOL;

# News Service

## General Information

Repository object can create news items to inform users on changes within the object.

These news do not have specific recipients. They are attached to the repository object and every user having read permission for the object can access the news.

Depending on the settings these news may be published by RSS feeds.

Every news item belongs to a _context_ being represented by a repository objects id (and type) and an optional subtype and id (e.g. a page in a learning module and its id).

## News Items

**Content and Title**

Content and title can both be either represented by a language variable or by a text string (without translation).

**News Priority**

Automatically created news MUST have a priority of `NEWS_NOTICE`, manually created news items MUST be indicated with a priority of `NEWS_MESSAGE`.

## Status of the Service

With ILIAS 5.4 the service is exposing a new interface through `$DIC->news()`. The interface tries to make the usage easy and clear, however it uses an older class ilNewsItem which is planned to be refactored in major parts in the future. Please avoid to use any deprecated methods of this class.

## Basic Usage

**Create**

```
$ns = $DIC->news();
$context = $ns->contextForRefId($ref_id);
$item = $ns->item($context);
$item->setTitle("Hello World");
$item->setContent("This is the news.");
$news_id = $ns->data()->save($item);
```

**Using Language Variables**

```
$ns = $DIC->news();
$context = $ns->contextForRefId($ref_id);
$item = $ns->item($context);
$item->setTitle("crs_create_xtst");
$item->setContentIsLangVar(true);		// title
$item->setContent("xtst_delete");
$item->setContentTextIsLangVar(true);	// content
$news_id = $ns->data()->save($item);
```

**Get News for Context**

```
$ns = $DIC->news();
$context = $ns->contextForRefId($ref_id);
$items = $ns->data()->getNewsOfContext($context);
```

**Update**

```
$ns = $DIC->news();
$context = $ns->contextForRefId($this->object->getRefId());
$items = $ns->data()->getNewsOfContext($context);
if ($n = current($items))
{
	$n->setContent(((int) $n->getContent()) + 1);
	$n->setContentTextIsLangVar(false);
	$ns->data()->save($n);
}
```


**Delete**

```
$ns = $DIC->news();
$context = $ns->contextForRefId($ref_id);
$items = $ns->data()->getNewsOfContext($context);
if ($n = current($items))
{
	$ns->data()->delete($n);
}
```
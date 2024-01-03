<?php

namespace MediaWiki\Extension\Subspace;

use MediaWiki\MediaWikiServices;
// use MediaWiki\Title\NamespaceInfo;
use NamespaceInfo;
use Title;
use Article;
use IContextSource;
use WikiPage;
// use Wikimedia\Rdbms\Subquery;

// global $kludgeVariable = "";

class SubspaceHookHandler implements
	\MediaWiki\Page\Hook\ArticleFromTitleHook,
	\MediaWiki\Page\Hook\WikiPageFactoryHook,
	\MediaWiki\Hook\TitleIsAlwaysKnownHook
{
	static function parseTitle($title)
	{
		$services = MediaWikiServices::getInstance();
		$namespaceInfo = $services->getNameSpaceInfo();
		if (($ns = $namespaceInfo->getCanonicalIndex(strtolower(str_replace(":", "_", $title)))) !== null)
			return Title::makeTitle($ns, "Main Page");
		if ($title->mArticleID > 0) return null;
		if (!str_contains($title->getBaseText(), ":")) return null;
		$explodedTitle = explode('#', $title->getFullText(), 2);
		$explodedTitle2 = explode('/', $explodedTitle[0], 2);
		$baseTitle = $explodedTitle2[0];
		$subpage = count($explodedTitle2) > 1 ? $explodedTitle2[1] : "";
		$anchor = count($explodedTitle) > 1 ? $explodedTitle[1] : "";

		$titleParts = explode(":", $baseTitle);
		for ($i = count($titleParts) - 1; $i > 0; $i--) {
			$namespace = implode(':', array_slice($titleParts, 0, $i));
			if (($ns = $namespaceInfo->getCanonicalIndex(strtolower(str_replace(":", "_", $namespace)))) !== null) {
				$pageTitle = implode(':', array_slice($titleParts, $i));
				if ($subpage !== "") $pageTitle .= "/" . $subpage;
				return Title::makeTitle($ns, $pageTitle, $anchor);
			}
		}
		return null;
	}

	public function onArticleFromTitle($title, &$article, $context)
	{
		// var_dump($title);
		$newTitle = (SubspaceHookHandler::parseTitle($title));
		if ($newTitle === null) return true;
		// var_dump($newTitle);
		// var_dump($newTitle->exists());
		$article = new Article($newTitle);
		return false;
	}

	public function onWikiPageFactory($title, &$page)
	{
		// var_dump($title);
		$newTitle = (SubspaceHookHandler::parseTitle($title));
		// var_dump($newTitle);
		if ($newTitle === null) return true;
		// var_dump($newTitle);
		// var_dump($newTitle->exists());
		$page = new WikiPage($newTitle);
		return false;
	}

	public function onTitleExists($title, &$exists)
	{
		// if (!str_contains($title->getFullText(), ":")) return true;
		// if (!str_contains($title->getFullText(), "RPG")) return true;
		var_dump($title);
		var_dump($exists);
		if ($exists) return true;
		$newTitle = (SubspaceHookHandler::parseTitle($title));
		if ($newTitle === null) return true;
		$exists = $newTitle->exists();
		return false;
	}

	public function onTitleIsAlwaysKnown($title, &$isKnown)
	{
		// echo $title;
		$isKnown = (SubspaceHookHandler::parseTitle($title) !== null);
		// echo $isKnown;
		return true;
	}
}

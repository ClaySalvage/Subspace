<?php

namespace MediaWiki\Extension\Subspace;

use MediaWiki\MediaWikiServices;
// use MediaWiki\Title\NamespaceInfo;
use NamespaceInfo;
use Title;
use Article;
use WikiPage;
// use Wikimedia\Rdbms\Subquery;

class SubspaceHookHandler implements \MediaWiki\Page\Hook\ArticleFromTitleHook, \MediaWiki\Page\Hook\WikiPageFactoryHook
{
	public function onArticleFromTitle($title, &$article, $context)
	{
		// var_dump($article);
		if ($title->mArticleID !== -1) return true;
		if (!str_contains($title->getBaseText(), ":")) return true;
		// var_dump($title->getBaseText());
		// var_dump($title->getFullText());
		// var_dump($title->getLocalURL());
		// var_dump($title->getPrefixedDBKey());
		// var_dump($title);
		$explodedTitle = explode('#', $title->getFullText(), 2);
		$explodedTitle2 = explode('/', $explodedTitle[0], 2);
		$baseTitle = $explodedTitle2[0];
		$subpage = count($explodedTitle2) > 1 ? $explodedTitle2[1] : "";
		$anchor = count($explodedTitle) > 1 ? $explodedTitle[1] : "";
		// echo ("//" . $baseTitle . "//" . $subpage . "//" . $anchor);
		$titleParts = explode(":", $baseTitle);
		// var_dump($titleParts);


		$services = MediaWikiServices::getInstance();
		$namespaceInfo = $services->getNameSpaceInfo();
		// echo "============================\n";
		// var_dump($context);
		for ($i = count($titleParts) - 1; $i > 0; $i--) {
			$namespace = implode(':', array_slice($titleParts, 0, $i));
			// echo $namespace . "\n";
			// echo $namespaceInfo->getCanonicalIndex(strtolower($namespace));
			// echo $namespaceInfo->getCanonicalIndex(strtolower(str_replace(":", "_", $namespace)));
			if (($ns = $namespaceInfo->getCanonicalIndex(strtolower(str_replace(":", "_", $namespace)))) !== null) {
				// echo $ns;
				$pageTitle = implode(':', array_slice($titleParts, $i));
				// if ($subpage !== "") $pageTitle .= "/" . $subpage;
				// echo "######" . $namespaceInfo->getCanonicalName($ns) . "#####" . $pageTitle . "######\n";
				$newTitle = Title::makeTitle($ns, $pageTitle, $anchor);
				// var_dump($newTitle);
				// $newarticle = Article::newFromTitle($newTitle, $context);
				$article = new Article($newTitle);
				// var_dump($article);
				return false;
			}
		}

		return true;
	}

	public function onWikiPageFactory($title, &$page)
	{
		if ($title->mArticleID !== -1) return true;
		if (!str_contains($title->getBaseText(), ":")) return true;
		$explodedTitle = explode('#', $title->getFullText(), 2);
		$explodedTitle2 = explode('/', $explodedTitle[0], 2);
		$baseTitle = $explodedTitle2[0];
		$subpage = count($explodedTitle2) > 1 ? $explodedTitle2[1] : "";
		$anchor = count($explodedTitle) > 1 ? $explodedTitle[1] : "";
		$titleParts = explode(":", $baseTitle);


		$services = MediaWikiServices::getInstance();
		$namespaceInfo = $services->getNameSpaceInfo();
		for ($i = count($titleParts) - 1; $i > 0; $i--) {
			$namespace = implode(':', array_slice($titleParts, 0, $i));
			if (($ns = $namespaceInfo->getCanonicalIndex(strtolower(str_replace(":", "_", $namespace)))) !== null) {
				$pageTitle = implode(':', array_slice($titleParts, $i));
				if ($subpage !== "") $pageTitle .= "/" . $subpage;
				$newTitle = Title::makeTitle($ns, $pageTitle, $anchor);
				$page = new WikiPage($newTitle);
				// var_dump($page);
				return false;
			}
		}

		return true;
	}
}

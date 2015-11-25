<?php

namespace Fliglio\Borg;

class Demo {

	private $go;
	private $http;

	public function __construct(Scheduler $go) {
		$this->go = $go;
	}


	public function getWordsForLinks(Entity $entity) {
		$links = $entity->bind(Links::class);

		$words = $this->go->makeChan(Scalar::class);
		$exits = $this->go->makeChan(Scalar::class);

		foreach ($links as $link) {
			$this->go->getWordsForLink($link, $words, $exits);
		}

		$success = true;

		$exitCount = 0;

		$allWords = [];

		$reader = $this->go->makeChanReader()
			->handle($words, function($word) {
				$allWords[] = $chanEntity;
			})
			->handle($exits, function($exit) {
				if ($chanEntity == false) {
					$success = false;
				}
				$exitCount++;
			});

		while ($exitCount < $links->length()) {
			$reader->next();
		}

		return array_count_values($allWords);
	}

	public function getWordsForLink(Link $link, Chan $words, Chan $exits) {
		$txt = $this->http->get($link->getHref());

		$wordsArr = split(' +', $txt);
		foreach ($wordsArr as $wordStr) {
			$words->send($wordStr);
		}
		$exits->send(true);
	}

}

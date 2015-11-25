<?php

namespace Fliglio\Borg;

use Fliglio\Borg\Type\Scalar;

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

		$allWords = [];
		$exitCount = 0;

		$reader = new ChanReader([$words, $exits]);
		while ($exitCount < $links->length()) {
			list($chanId, $chEntity) = $reader->next();

			switch ($chanId) {
			case $words->getId():
				$allWords[] = $word;
				break;
			case $exits->getId():
				$exitCount++;
				break;
			default:
				usleep(200); // 200 microseconds
			}
		}

		$exits->close();
		$words->close();

		return array_count_values($allWords);
	}

	public function getWordsForLink(Link $link, Chan $words, Chan $exits) {
		$txt = $this->http->get($link->getHref());

		$wordsArr = split(' +', $txt);
		foreach ($wordsArr as $wordStr) {
			$words->push($wordStr);
		}
		$exits->push(true);
	}

}

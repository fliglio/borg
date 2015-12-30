<?php

namespace Fliglio\Borg;

use Fliglio\Web\GetParam;

use Fliglio\Borg\BorgImplant;


class ShakespeareResource {
	use BorgImplant;
	
	private $urls = [
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-alls-11.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-antony-23.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-as-12.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-comedy-7.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-coriolanus-24.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-cymbeline-17.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-first-51.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-hamlet-25.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-julius-26.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-king-45.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-life-54.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-life-55.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-life-56.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-lovers-62.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-loves-8.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-macbeth-46.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-measure-13.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-merchant-5.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-merry-15.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-midsummer-16.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-much-3.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-othello-47.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-pericles-21.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-rape-61.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-romeo-48.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-second-52.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-sonnets-59.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-taming-2.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-tempest-4.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-third-53.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-timon-49.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-titus-50.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-tragedy-57.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-tragedy-58.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-troilus-22.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-twelfth-20.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-two-18.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-venus-60.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/shakespeare-winters-19.txt',
		'https://raw.githubusercontent.com/benschw/shakespeare-txt/master/sonnets.txt',
	];


	public function allWordsSync() {
		$words = [];
		foreach ($this->urls as $url) {
			$txt = $this->downloadUrl($url);
			$arr = $this->misspellingsOnly($this->toWords($this->removePunctuation($txt)));
			
			$words = array_merge($words, $arr);
		}
		return $words;
	}

	public function allWords() {
		$txts = $this->getTextsFromLinks($this->urls);

		$words = $this->getWordsFromText($txts, count($this->urls));

		return $this->aggregateWords($words, count($this->urls));
	}

	// Download texts and push onto chan
	private function getTextsFromLinks(array $urls) {
		$txts = $this->coll()->mkchan();

		foreach ($urls as $url) {
			$this->coll()->generateText($url, $txts);
		}
		return $txts;
	}
	public function generateText($url, Chan $txts) {
		error_log("GENERATE TEXT");
		$txts->add($this->downloadUrl($url));
	}
	// Consume texts chan and push words onto chan
	private function getWordsFromText(Chan $txts, $numTxts) {
		$words = $this->coll()->mkchan();

		$this->coll()->generateWords($txts, $words, $numTxts);
		
		return $words;
	}
	public function generateWords(Chan $txts, Chan $words, $numTxts) {
		error_log("GENERATE WORDS");
		for ($i = 0; $i < $numTxts; $i++) {
			$txt = $txts->get();

			$arr = $this->misspellingsOnly($this->toWords($this->removePunctuation($txt)));
			
			$words->add($arr);
		}
	}
	// Aggregate words
	private function aggregateWords(Chan $words, $numWords) {
		$wordsArr = [];
		for ($i = 0; $i < $numWords; $i++) {
			$wordsArr = array_merge($wordsArr, $words->get());
		}

		return $wordsArr;
	}


	// Common algorithm methods
	private function downloadUrl($url) {
		return file_get_contents($url);
	}
	private function removePunctuation($txt) {
		return $txt;
		$pattern = '/[^\w\s]/';

		return preg_replace($pattern, '', $txt);
	}
	private function toWords($txt) {
		$pattern = '/[\W]+/';
		return preg_split($pattern, $txt);
	}
	private function misspellingsOnly(array $arr) {
		$pspell = pspell_new("en", "", "", "", (PSPELL_FAST|PSPELL_RUN_TOGETHER));
		$miss = [];
		foreach ($arr as $w) {
			if (!pspell_check($pspell, $w)) {
				$miss[] = $w;
			}
		}
		return $miss;
	}
}

[![Build Status](https://travis-ci.org/fliglio/borg.svg?branch=master)](https://travis-ci.org/fliglio/borg)
[![Latest Stable Version](https://poser.pugx.org/fliglio/borg/v/stable.svg)](https://packagist.org/packages/fliglio/borg)



# Fliglio.Borg

`Fliglio\Borg` is an implementation of [goroutines]() (made popular by [golang]()) to provide a distributed parallel
concurrency framework for PHP.

If you're a member of the Borg Collective, you don't do anything by yourself. You don't make decisions by yourself, you
don't solve problems by yourself, and you certainly don't do work by yourself - you distribute the load across the collective!

And now you can too.

## Usage

### Parallel Work
When the Borg encouter a group of unassimilated humans, the first thing they have to decide is whether or not they are a threat.
One way to do this would be to look up the humans on Wikipedia, do some analysis for their threat level, and make a decision.
But if you're a member of the Borg, you don't have to do this alone, you can farm out the work and have one member of the
collective do the reasearch for each human and report back, and then aggregate the sum information to make a decision.

class ThreatAssessment {
	use BorgImplant;

	public function assessThreatOfGroup(Entity $e) {
		$names = $e->bind(Names::getClass());

		$indicators = $this->mkchan(ThreatIndicator::getClass());

		foreach ($names as $name) {
			$this->coll()->assessThreatOfHuman($name, $indicators, $exits)
		}

	}

}



### Distributed Work
Now if a Borg decides there is a particularly threatening human, they might want to make sure they report it directly to
the core of the collective and not rely on only notifying their team (what if all their team dies before the knowledge
propogates to their main force!)


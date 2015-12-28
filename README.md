[![Build Status](https://travis-ci.org/fliglio/borg.svg?branch=master)](https://travis-ci.org/fliglio/borg)
[![Latest Stable Version](https://poser.pugx.org/fliglio/borg/v/stable.svg)](https://packagist.org/packages/fliglio/borg)



# Fliglio.Borg

`Fliglio\Borg` is an implementation of [goroutines]() (made popular by [golang]()) to provide a distributed parallel
concurrency framework for PHP.

If you're a member of the Borg Collective, you don't do anything by yourself. You don't make decisions by yourself, you
don't solve problems by yourself, and you certainly don't do work by yourself - you distribute the load across the collective!

And now you can too.







### TODO / Caveats

#### Next

- support sending `null` to Chans and Collective Routines
	- make match typical php behavior ([see here](http://artur.ejsmont.org/blog/content/php-typehints-causing-errors-when-null-gets-passed-in))

#### Other

- Collective Routines must be exact types. You can't hint your method with an interface and pass in an
  implementation (the hint is used to unmarshal the argument.)
- Only Collective Routines are multi-datacenter aware. Chans can only be used in your local datacenter.
- Though Chan ordering is always guaranteed, ChanReader introduces some weirdness where messages published to
  different Chans might be read out of order (see below)
- `Chan::get` is implemented by polling `basic_get`. It would be nice to iteratively use `basic_consume`

#### ChanReader
There is a race condition with ChanReaders where order between channels can't be guaranteed.
In the following example, even though `$ex` is added to after all numbers have been added to `$ch`,
The `$ex` value might arraive in the `ChanReader` first. In this situation, consider
sending a `null` to `$ch` to signal that the work is done.



	public function generateNumbers(GetParam $limit) {
		$ch = $this->mkChan();
		$ex = $this->mkChan();

		$this->coll()->gen($ch, $ex, $limit->get());
		
		$nums = [];

		$r = $this->coll()->mkChanReader([$ch, $ex]);
		while (true) {
			list($id, $val) = $r->get();
			switch ($id) {
			case $ch->getId():
				$nums[] = $val;
				break;
			case $ex->getId():
				return $nums;
			}
		}
	}
	public function gen(Chan $ch, Chan $ex, $limit) {
		for ($i = 0; $i <= $limit; $i++) {
			$ch->add($i);
		}

		// sleep for a second to avoid the possibility that the $ex value is read before $ch's last value
		sleep(1); 
		
		$ex->add(true);
	}

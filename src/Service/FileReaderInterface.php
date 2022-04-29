<?php

interface OfferInterface {}

/**
* Interface for The Collection class that contains Offers
*/
interface OfferCollectionInterface {
	public function get(int $index): OfferInterface;
	public function getIterator(): iterable;
	//public function save(OfferInterface, $offer);
}

/**
* The interface provides the contract for different readers
* E.g. it can be XML/JSON Remote Endpoint, or CSV/JSON/XML local files
*/
interface ReaderInterface {
	/**
	* Read in incoming data and parse to objects
	*/
	public function read(string $input): OfferCollectionInterface;

	//public function write(string $input): OfferCollectionInterface;
}

/**
 * Csv Reader
 */
class Csv implements ReaderInterface {
	public function read(string $input): OfferCollectionInterface
	{
		$inputData = json_decode($input, true);
		return new OfferCollection($inputData);
	}

	// public function write(array $input): OfferCollectionInterface
	// {
	// 	// Open a file in write mode ('w')
	// 	$fp = fopen('persons.csv', 'w');
		
	// 	// Loop through file pointer and a line
	// 	foreach ($list as $fields) {
	// 		fputcsv($fp, $fields);
	// 	}
		
	// 	fclose($fp);
	// }
}

/**
 * Json Reader
 */
class Json implements ReaderInterface {
	public function read(string $input): OfferCollectionInterface
	{
		$inputData = json_decode($input, true);
		return new OfferCollection($inputData);
	}
}

/**
 * Json Reader
 */
class Xml implements ReaderInterface {
	public function read(string $input): OfferCollectionInterface
	{
		$xml = simplexml_load_string($input,'JsonXMLElement');
		$json = json_encode($xml);
		$inputData = json_decode($json,TRUE);
		//$inputData = json_decode($input, true);
		return new OfferCollection($inputData);
	}
}

class FileReader {
	public function getData(ReaderInterface $type, string $data)
	{
		return $type->read($data);
	}

	// public function saveData(ReaderInterface $type, string $data)
	// {
	// 	return $type->write($data);
	// }
}

/**
 * OfferCollection
 */
class OfferCollection implements OfferCollectionInterface {
	public $offer;

	/**
	 * Constructor
	 *
	 * @param Array $offer
	 */
	public function __construct(array $offer)
	{
		$this->offer = $offer;
	}

	/**
	 * Get Offer
	 *
	 * @param integer $index IndexOF offer
	 *
	 * @return OfferInterface Offer
	 */
	public function get(int $index): OfferInterface
	{
		return new offer($this->offer[$index]);
	}

	/**
	 * Get offer Iterator
	 *
	 * @return iterable Offers
	 */
	public function getIterator(): iterable
	{
		return $this->offer;
	}

	// public function save(OfferInterface, $offer)
    // {
    //     if(!$offer instanceof OfferInterface)
    //         throw new \InvalidArgumentException('...');

    //     echo 'works!';
    // }
}
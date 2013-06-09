<?php
namespace PhalconRest\Responses;

class CSVResponse extends Response{

	protected $headers = true;

	public function __construct(){
		parent::__construct();
	}

	public function send($records){

		$response = $this->di->get('response');
		// Headers for a CSV
		$response->setHeader('Content-type', 'application/csv');

		// By default, filename is just a timestamp. You should probably change this.
		$response->setHeader('Content-Disposition', 'attachment; filename="'.time().'.csv"');
		$response->setHeader('Pragma', 'no-cache');
		$response->setHeader('Expires', '0');
		
		// We write directly to out, which means we don't ever save this file to disk.
		$handle = fopen('php://output', 'w');

		// The keys of the first result record will be the first line of the CSV (headers)
		if($this->headers){
			fputcsv($handle, array_keys($records[0]));
		}

		// Write each record as a csv line.
		foreach($records as $line){
			fputcsv($handle, $line);
		}

		fclose($handle);

		return $this;
	}

	public function useHeaderRow($headers){
		$this->headers = (bool) $headers;
		return $this;
	}

}
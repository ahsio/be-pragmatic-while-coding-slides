<?php

	/* 
	 * ------------------------
	 * Principe ouvert - fermé
	 * ------------------------
	 */
	
	$importer = new DataImporter(new CsvFileLoader(), new MySQLGateway());
	$importer = new DataImporter(new XmlFileLoader(), new MongoGateway());
	$importer = new DataImporter(new JsonFileLoader(), new ElasticSearchGateway());
	
	/* 
	 * ----------------------------
	 *  Liskov (valeurs retournées)
	 * ----------------------------
	 */

	abstract class AbstractLoader implements FileLoader
	{	
		public function load($file)
		{
			if (!file_exists($file)) {
				throw new \InvalidArgumentException(sprintf('%s does not exist.', $file));
			}

			return [];
		}
	}

	class CsvFileLoader extends AbstractLoader
	{
		public function load($file)
		{
			$records = parent::load($file);

			if (false !== $handle = fopen($file, 'r')) {
				while ($record = fgetcsv($handle)) {
					$records[] = $record;
				}
			}
			fclose($handle);

			return $records;
		}
	}
	
	/* 
	 * ----------------------------
	 *  Ségrégation d'interfaces
	 * ----------------------------
	 */

	 interface UrlGeneratorInterface
	{
		public function generate($name, $parameters = array());
	}

	interface UrlMatcherInterface
	{
		public function match($pathinfo);
	}

	interface RouterInterface extends UrlMatcherInterface, UrlGeneratorInterface
	{
		public function getRouteCollection();
	}
	
	
	/* 
	 *  Utilité pour les extensions
	 */
	 
	use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

	class RoutingExtension extends \Twig_Extension
	{
		private $generator;

		public function __construct(UrlGeneratorInterface $generator)
		{
			$this->generator = $generator;
		}

		public function getPath($name, $parameters = array())
		{
			return $this->generator->generate($name, $parameters);
		}
	}
		
    /* 
	 *  Injection de dépendences
	 */
	class DataImporter
	{
		private $loader;
		private $gateway;

		public function __construct()
		{
			$this->loader  = new CsvFileLoader();
			$this->gateway = new DataGateway();
		}
	}
	
	// Then
	// ------------------------
	class DataImporter
	{
		private $loader;
		private $gateway;

		public function __construct(CsvFileLoader $loader, DataGateway $gateway)
		{
			$this->loader  = $loader;
			$this->gateway = $gateway;
		}
	}
	
	// Then
	// ------------------------
	class DataImporter
	{
		private $loader;
		private $gateway;

		public function __construct(FileLoader $loader, Gateway $gateway)
		{
			$this->loader  = $loader;
			$this->gateway = $gateway;
		}
	}
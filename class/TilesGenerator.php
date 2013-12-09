<?php

require_once(dirname(__FILE__).'/../constants.inc.php');

class TilesGeneratorException extends Exception {}
class TilesGeneratorRightsException extends TilesGeneratorException {}
class TilesGeneratorScriptException extends TilesGeneratorException {}

class TilesGenerator {
	private $panorama;

	const SCRIPT_RELATIVE_PATH = 'to_tiles/gen_tiles.sh';

	public function __construct($img_path, $panorama) {
		/** @param $img_path : path of the source image
		 *  @param $panorama : a site_point instance
		 */
		$this->panorama = $panorama;
		$this->img_path = $img_path;
	}

	public function prepare() {
		$err = false;
		$pano_path = $this->panorama->tiles_path();

		if (! is_dir(PANORAMA_PATH)) {
			if (! mkdir(PANORAMA_PATH)) {
				$err = "le répertoire \"PANORAMA_PATH\" n'est pas accessible et ne peut être créé";
			}
		} else if (file_exists($pano_path)) {
			$err = sprintf("le nom de répertoire \"%s\" est déjà pris",
			               $pano_path);
		} else {
			mkdir($pano_path);
		}
		if ($err) {
			throw (new TilesGeneratorRightsException($err));
		}

	}

	public function mk_command() {
		/** Returns the command to execute
		 */
		$c = sprintf('%s/%s -p "%s" "%s"',
		             CELUTZ_PATH, $this::SCRIPT_RELATIVE_PATH,
		             $this->panorama->tiles_prefix(), $this->img_path);
		return escapeshellcmd($c);
	}

	public function process() {
		$err = false;
		if ($fp = popen($this->mk_command(), 'r')) {
			while (!feof($fp)) {
				$results = fgets($fp, 4096);
				if (strlen($results) == 0) {
					// stop the browser timing out
					flush();
				} else {
					$tok = strtok($results, "\n");
					while ($tok !== false) {
						echo htmlspecialchars(sprintf("%s\n",$tok))."<br/>";
						flush();
						$tok = strtok("\n");
					}
				}
			}
			if (pclose($fp) !== 0) {
				$err = "Opération en échec durant l'exécution du script";
			}
		} else {
			$err =  'Opération en échec à l\'ouverture du script !';
		}

		if ($err) {
			throw (new TilesGeneratorScriptException($err));
		}
	}
}

?>

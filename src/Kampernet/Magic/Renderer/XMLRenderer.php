<?php
namespace Kampernet\Magic\Renderer;

use Kampernet\Magic\Base\Renderer\RenderInterface;
use Kampernet\Magic\Base\ResponseContent;
use DOMNode;
use DOMDocument;
use Iterator;
use Kampernet\Magic\Base\Model\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * XMLRenderer for getting the response as XML.
 * needs a bit of work yet, currently auto underscores
 * from camel case, needa fix that I think.  should
 * just be whatever the object is.
 *
 * @package Kampernet\Magic\Renderer
 */
class XMLRenderer implements RenderInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::sendHeaders()
	 */
	public function sendHeaders(Response $response) {

		$response->headers = new ResponseHeaderBag([
			"Content-Type" => "text/xml",
			"Cache-Control" => "no-cache, must-revalidate",
			"Expires" => "Sat, 26 Jul 1997 05:00:00 GMT"
		]);

		$response->sendHeaders();
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::render()
	 */
	public function render(Request $request, Response $response, ResponseContent $content) {

		$response->setContent($this->to_domdocument($content)->saveXML());

		return $response;
	}

	/**
	 * appends nodes to the dom
	 *
	 * @param DOMNode $node
	 * @param null $tag
	 * @param ResponseContent $response
	 * @return void
	 */
	private function append_to(DOMNode $node, $tag = null, ResponseContent $response) {

		$doc = ($node instanceof DOMDocument) ? $node : $node->ownerDocument;
		$tag = (is_null($tag)) ? "response" : $tag;

		$new_node = $doc->createElement($tag);
		$node->appendChild($new_node);

		foreach (get_object_vars($response) as $name => $value) {
			self::append_variable($name, $value, $new_node);
		}


	}

	/**
	 * appends variables to the xml
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param DOMNode $parent_node
	 */
	protected static function append_variable($name, $value, $parent_node) {

		$new_node = null;
		$name = str_replace('-', '_', $name);
		if (substr($name, 0, 1) != '_' && $value !== false) {
			if (is_array($value) or $value instanceof Iterator) {
				if (!preg_match('/^[A-Za-z]/', $name)) {
					$name = 'element' . $name;
				}
				self::append_array($value, $name, $parent_node);
			} elseif (is_object($value) && $value instanceof Model) {
				$name = $value->getOriginalClassName();
				$name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
				self::append_array(get_object_vars($value), $name, $parent_node);
			} else {
				$new_node = $parent_node->ownerDocument->createElement($name); //can't pass the value here: http://bugs.php.net/bug.php?id=31191
				$text_node = $parent_node->ownerDocument->createTextNode((string) $value);
				$new_node->appendChild($text_node);
			}
		}
		if (isset($new_node)) {
			$parent_node->appendChild($new_node);
		}
	}

	/**
	 * appends an array to the dom
	 *
	 * @param array $array
	 * @param string $tag
	 * @param DOMNode $parent_node
	 */
	protected static function append_array($array, $tag, $parent_node) {

		if ($tag) {
			$outer_element = $parent_node->ownerDocument->createElement($tag);
			$parent_node->appendChild($outer_element);
		} else {
			$outer_element = $parent_node;
		}

		foreach ($array as $name => $value) {
			if (!preg_match('/^[A-Za-z]/', $name)) {
				$name = 'element' . $name;
			} else {
				$name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
			}
			self::append_variable($name, $value, $outer_element);
		}
	}


	/**
	 * creates a dom document xml version of the object
	 *
	 * @param ResponseContent $response
	 * @return DOMDocument
	 */
	public function to_domdocument(ResponseContent $response) {

		$doc = new DOMDocument();
		$this->append_to($doc, 'response', $response);

		return $doc;
	}

}
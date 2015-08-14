<?php

/**
* Made with ❤ by nostrzak
*
* XML Tools
*/

namespace Chucky\Tool;

class XML
{
  /**
   * Clean the XML from all empty nodes
   *
   * @param $xml Raw XML string
   * @return string XML without empty nodes
   */
  public static function remove_empty_nodes($xml)
  {
    $doc = new \DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->loadXML($xml);

    $xpath = new \DOMXPath($doc);

    foreach($xpath->query('//*[not(node())]') as $node ) {
      $node->parentNode->removeChild($node);
    }

    $doc->formatOutput = true;
    return $doc->saveXML();
  }
}

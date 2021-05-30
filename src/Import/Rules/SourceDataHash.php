<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @category    BibTeX
 * @package     Opus\Bibtex\Import\Rules
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace Opus\Bibtex\Import\Rules;

use function array_key_exists;
use function array_keys;
use function json_encode;
use function ksort;
use function strpos;

/**
 * Setzt den Hashwert des importierten BibTeX-Record als Wert des Enrichments opus.import.dataHash.
 * Hierbei wird standardmäßig die MD5-Hashfunktion verwendet.
 */
class SourceDataHash extends AbstractComplexRule
{
    /**
     * Name des Enrichments, in dem der Hashwert (auf Basis der Hashfunktion HASH_FUNCTION) des importierten
     * BibTeX-Records gespeichert wird
     */
    const SOURCE_DATA_HASH_KEY = 'opus.import.dataHash';

    /**
     * Name der Hashfunktion, die zur Bestimmung des Hashwerts verwendet werden soll.
     *
     * TODO konfigurierbar machen?
     */
    const HASH_FUNCTION = 'md5';

    /**
     * @param array $fieldValues
     * @param array $documentMetadata
     */
    protected function setFields($fieldValues, &$documentMetadata)
    {
        // Spezialfelder des Parsers sollen bei der Hashwert-Bestimmung nicht betrachtet werden
        foreach (array_keys($fieldValues) as $key) {
            if (strpos($key, '_') === 0) {
                unset($fieldValues[$key]);
            }
        }

        // Sortierung der Schlüssel
        ksort($fieldValues);

        // json_encode ist schneller als serialize und erzeugt ein kompakteres Ergebnis
        $hashValue = self::HASH_FUNCTION . ':' . (self::HASH_FUNCTION)(json_encode($fieldValues));

        $enrichments   = array_key_exists('Enrichment', $documentMetadata) ? $documentMetadata['Enrichment'] : [];
        $enrichments[] = [
            'KeyName' => self::SOURCE_DATA_HASH_KEY,
            'Value'   => $hashValue,
        ];

        $documentMetadata['Enrichment'] = $enrichments;
    }
}

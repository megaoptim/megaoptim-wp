<?php
/********************************************************************
 * Copyright (C) 2021 MegaOptim (https://megaoptim.com)
 *
 * This file is part of MegaOptim Image Optimizer
 *
 * MegaOptim Image Optimizer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MegaOptim Image Optimizer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MegaOptim Image Optimizer. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

namespace MegaOptim\Client\Http\Multipart;

/**
 * A multipart/form-data object.
 */
final class MultipartFormData extends Multipart {

    /**
     * Creates a new multipart/form-data object.
     * @param string $boundary the multipart boundary. If empty a new boundary will be generated.
     */
    public function __construct($boundary = '') {
        parent::__construct($boundary, 'multipart/form-data');
    }

	/**
	 * Adds a string parameter.
	 *
	 * @param  string  $name  the parameter name.
	 * @param  string  $value  the parameter value.
	 *
	 * @return MultipartFormData this object.
	 */
    public function addValue($name, $value) {
        Util::validateNonEmptyString($name, '$name');
        Util::validateString($value, '$value');

        $this->startPart();
        $this->addContentDisposition('form-data', $name);
        $this->endHeaders();
        $this->addContent($value);
        $this->endPart();

        return $this;
    }

    /**
     * Adds a file parameter
     * @param string $name the parameter name.
     * @param string $filename the name of the file.
     * @param string|resource|callable $content the file's content.
     *        If it's a callable it should take a length argument and return a string that is not larger than the input.
     * @param string $contentType the file's content type.
     * @param int $contentLength the file's content length, or -1 if not known. Ignored if the file's content is a string.
     * @return MultipartFormData this object.
     */
    public function addFile($name, $filename, $content, $contentType, $contentLength = -1) {
        Util::validateNonEmptyString($name, '$name');
        Util::validateNonEmptyString($filename, '$filename');
        Util::validateStreamable($content, '$content');
        Util::validateNonEmptyString($contentType, '$contentType');
        Util::validateInt($contentLength, '$contentLength');

        $this->startPart();
        $this->addContentDisposition('form-data', $name, $filename);
        $this->addContentType($contentType);
        $this->endHeaders();
        $this->addContent($content, $contentLength);
        $this->endPart();

        return $this;
    }
}

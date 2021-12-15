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
 * A multipart/alternative object.
 */
final class MultipartAlternative extends Multipart {

    /**
     * Creates a new multipart/alternative object.
     * @param string $boundary the multipart boundary. If empty a new boundary will be generated.
     */
    public function __construct($boundary = '') {
        parent::__construct($boundary, 'multipart/alternative');
    }

    /**
     * Adds a multipart alternative.
     * @param Multipart $multipart The nested multipart.
     * @return MultipartAlternative this object.
     */
    public function addMultipart(Multipart $multipart) {
        $this->startPart();
        $this->addContentType($multipart->getContentType());
        $this->endHeaders();
        $this->addContent(array($multipart, 'read'), $multipart->getContentLength());
        $this->endPart();

        return $this;
    }

    /**
     * Adds an alternative.
     * @param string|resource|callable $content the part's content.
     *        If it's a callable it should take a length argument and return a string that is not larger than the input.
     * @param string $contentType the part's content type.
     * @param int $contentLength the part's content length, or -1 if not known. Ignored if the part's content is a string.
     * @param string $contentTransferEncoding the optional content transfer encoding.
     * @return MultipartAlternative this object.
     */
    public function addPart($content, $contentType, $contentLength = -1, $contentTransferEncoding = '') {
        Util::validateStreamable($content, '$content');
        Util::validateNonEmptyString($contentType, '$contentType');
        Util::validateInt($contentLength, '$contentLength');
        Util::validateString($contentTransferEncoding, '$contentTransferEncoding');

        $this->startPart();
        $this->addContentType($contentType);
        if ($contentTransferEncoding !== '') {
            $this->addContentTransferEncoding($contentTransferEncoding);
        }
        $this->endHeaders();
        $this->addContent($content, $contentLength);
        $this->endPart();

        return $this;
    }
}

<?php

namespace App\Services;

/** Satu baris data tidak valid — dicatat ke laporan, bukan fatal. */
class GuruImportRowException extends \RuntimeException
{
}

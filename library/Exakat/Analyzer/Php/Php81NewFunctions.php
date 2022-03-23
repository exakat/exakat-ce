<?php declare(strict_types = 1);
/*
 * Copyright 2012-2022 Damien Seguy – Exakat SAS <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Common\FunctionDefinition;

class Php81NewFunctions extends FunctionDefinition {
    protected $phpVersion = '8.1-';

    public function analyze(): void {
        $this->functions = array('\enum_exists',
                                 '\array_is_list',
                                 '\fsync',
                                 '\fdatasync',
                                 '\imagecreatefromavif',
                                 '\imageavif',
                                 '\mysqli_fetch_column',
                                 '\sodium_crypto_core_ristretto255_add',
                                 '\sodium_crypto_core_ristretto255_from_hash',
                                 '\sodium_crypto_core_ristretto255_is_valid_point',
                                 '\sodium_crypto_core_ristretto255_random',
                                 '\sodium_crypto_core_ristretto255_scalar_add',
                                 '\sodium_crypto_core_ristretto255_scalar_complement',
                                 '\sodium_crypto_core_ristretto255_scalar_invert',
                                 '\sodium_crypto_core_ristretto255_scalar_mul',
                                 '\sodium_crypto_core_ristretto255_scalar_negate',
                                 '\sodium_crypto_core_ristretto255_scalar_random',
                                 '\sodium_crypto_core_ristretto255_scalar_reduce',
                                 '\sodium_crypto_core_ristretto255_scalar_sub',
                                 '\sodium_crypto_core_ristretto255_sub',
                                 '\sodium_crypto_scalarmult_ristretto255',
                                 '\sodium_crypto_scalarmult_ristretto255_base',
                                 '\sodium_crypto_stream_xchacha20',
                                 '\sodium_crypto_stream_xchacha20_keygen',
                                 '\sodium_crypto_stream_xchacha20_xor',
                                );
        parent::analyze();
    }
}

?>

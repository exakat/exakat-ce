<?php
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

use Exakat\Analyzer\Analyzer;

class Php80RemovesResources extends Analyzer {
    public function analyze() : void {
        // $a = enchant_broker_init()
        // if (is_resource($a))
        $this->atomFunctionIs(array('\\enchant_broker_init',
                                    '\\enchant_broker_request_dict',
                                    '\\enchant_broker_request_pwl_dict',
                                    '\\openssl_x509_read',
                                    '\\openssl_csr_sign',
                                    '\\openssl_csr_new',
                                    '\\openssl_pkey_new',
                                    '\\socket_create',
                                    '\\socket_create_listen',
                                    '\\socket_accept',
                                    '\\socket_import_stream',
                                    '\\socket_addrinfo_connect',
                                    '\\socket_addrinfo_bind',
                                    '\\socket_wsaprotocol_info_import',
                                    '\\msg_get_queue',
                                    '\\sem_get',
                                    '\\shm_attach',
                                    '\\xml_parser_create',
                                    '\\xml_parser_create_ns',
                                    '\\inflate_init',
                                    '\\deflate_init',
                                    '\\curl_init',
                                    '\\curl_multi_init',
                                    '\\curl_share_init',
                                    '\\shmop_open',
                                    ))
             ->inIs('RIGHT')
             ->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('LEFT')
             ->atomIs(self::CONTAINERS)
             ->inIs('DEFINITION')
             ->outIs('DEFINITION')
             ->inIs('ARGUMENT')
             ->functioncallIs('\\is_resource');
        $this->prepareQuery();
    }
}

?>

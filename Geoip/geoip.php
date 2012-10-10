<?php

/* -*- Mode: C; indent-tabs-mode: t; c-basic-offset: 2; tab-width: 2 -*- */
/* geoip.inc
 *
 * Copyright (C) 2007 MaxMind LLC
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

define("GEOIP_COUNTRY_BEGIN", 16776960);
define("GEOIP_STATE_BEGIN_REV0", 16700000);
define("GEOIP_STATE_BEGIN_REV1", 16000000);
define("GEOIP_STANDARD", 0);
define("GEOIP_MEMORY_CACHE", 1);
define("GEOIP_SHARED_MEMORY", 2);
define("STRUCTURE_INFO_MAX_SIZE", 20);
define("DATABASE_INFO_MAX_SIZE", 100);
define("GEOIP_COUNTRY_EDITION", 106);
define("GEOIP_PROXY_EDITION", 8);
define("GEOIP_ASNUM_EDITION", 9);
define("GEOIP_NETSPEED_EDITION", 10);
define("GEOIP_REGION_EDITION_REV0", 112);
define("GEOIP_REGION_EDITION_REV1", 3);
define("GEOIP_CITY_EDITION_REV0", 111);
define("GEOIP_CITY_EDITION_REV1", 2);
define("GEOIP_ORG_EDITION", 110);
define("GEOIP_ISP_EDITION", 4);
define("SEGMENT_RECORD_LENGTH", 3);
define("STANDARD_RECORD_LENGTH", 3);
define("ORG_RECORD_LENGTH", 4);
define("MAX_RECORD_LENGTH", 4);
define("MAX_ORG_RECORD_LENGTH", 300);
define("GEOIP_SHM_KEY", 0x4f415401);
define("US_OFFSET", 1);
define("CANADA_OFFSET", 677);
define("WORLD_OFFSET", 1353);
define("FIPS_RANGE", 360);
define("GEOIP_UNKNOWN_SPEED", 0);
define("GEOIP_DIALUP_SPEED", 1);
define("GEOIP_CABLEDSL_SPEED", 2);
define("GEOIP_CORPORATE_SPEED", 3);

class GeoIP {
    public $flags;
    public $filehandle;
    public $memory_buffer;
    public $databaseType;
    public $databaseSegments;
    public $record_length;
    public $shmid;
	
    public $GEOIP_COUNTRY_CODE_TO_NUMBER = array(
	"" => 0, "AP" => 1, "EU" => 2, "AD" => 3, "AE" => 4, "AF" => 5, 
	"AG" => 6, "AI" => 7, "AL" => 8, "AM" => 9, "AN" => 10, "AO" => 11, 
	"AQ" => 12, "AR" => 13, "AS" => 14, "AT" => 15, "AU" => 16, "AW" => 17, 
	"AZ" => 18, "BA" => 19, "BB" => 20, "BD" => 21, "BE" => 22, "BF" => 23, 
	"BG" => 24, "BH" => 25, "BI" => 26, "BJ" => 27, "BM" => 28, "BN" => 29, 
	"BO" => 30, "BR" => 31, "BS" => 32, "BT" => 33, "BV" => 34, "BW" => 35, 
	"BY" => 36, "BZ" => 37, "CA" => 38, "CC" => 39, "CD" => 40, "CF" => 41, 
	"CG" => 42, "CH" => 43, "CI" => 44, "CK" => 45, "CL" => 46, "CM" => 47, 
	"CN" => 48, "CO" => 49, "CR" => 50, "CU" => 51, "CV" => 52, "CX" => 53, 
	"CY" => 54, "CZ" => 55, "DE" => 56, "DJ" => 57, "DK" => 58, "DM" => 59, 
	"DO" => 60, "DZ" => 61, "EC" => 62, "EE" => 63, "EG" => 64, "EH" => 65, 
	"ER" => 66, "ES" => 67, "ET" => 68, "FI" => 69, "FJ" => 70, "FK" => 71, 
	"FM" => 72, "FO" => 73, "FR" => 74, "FX" => 75, "GA" => 76, "GB" => 77,
	"GD" => 78, "GE" => 79, "GF" => 80, "GH" => 81, "GI" => 82, "GL" => 83, 
	"GM" => 84, "GN" => 85, "GP" => 86, "GQ" => 87, "GR" => 88, "GS" => 89, 
	"GT" => 90, "GU" => 91, "GW" => 92, "GY" => 93, "HK" => 94, "HM" => 95, 
	"HN" => 96, "HR" => 97, "HT" => 98, "HU" => 99, "ID" => 100, "IE" => 101, 
	"IL" => 102, "IN" => 103, "IO" => 104, "IQ" => 105, "IR" => 106, "IS" => 107, 
	"IT" => 108, "JM" => 109, "JO" => 110, "JP" => 111, "KE" => 112, "KG" => 113, 
	"KH" => 114, "KI" => 115, "KM" => 116, "KN" => 117, "KP" => 118, "KR" => 119, 
	"KW" => 120, "KY" => 121, "KZ" => 122, "LA" => 123, "LB" => 124, "LC" => 125, 
	"LI" => 126, "LK" => 127, "LR" => 128, "LS" => 129, "LT" => 130, "LU" => 131, 
	"LV" => 132, "LY" => 133, "MA" => 134, "MC" => 135, "MD" => 136, "MG" => 137, 
	"MH" => 138, "MK" => 139, "ML" => 140, "MM" => 141, "MN" => 142, "MO" => 143, 
	"MP" => 144, "MQ" => 145, "MR" => 146, "MS" => 147, "MT" => 148, "MU" => 149, 
	"MV" => 150, "MW" => 151, "MX" => 152, "MY" => 153, "MZ" => 154, "NA" => 155,
	"NC" => 156, "NE" => 157, "NF" => 158, "NG" => 159, "NI" => 160, "NL" => 161, 
	"NO" => 162, "NP" => 163, "NR" => 164, "NU" => 165, "NZ" => 166, "OM" => 167, 
	"PA" => 168, "PE" => 169, "PF" => 170, "PG" => 171, "PH" => 172, "PK" => 173, 
	"PL" => 174, "PM" => 175, "PN" => 176, "PR" => 177, "PS" => 178, "PT" => 179, 
	"PW" => 180, "PY" => 181, "QA" => 182, "RE" => 183, "RO" => 184, "RU" => 185, 
	"RW" => 186, "SA" => 187, "SB" => 188, "SC" => 189, "SD" => 190, "SE" => 191, 
	"SG" => 192, "SH" => 193, "SI" => 194, "SJ" => 195, "SK" => 196, "SL" => 197, 
	"SM" => 198, "SN" => 199, "SO" => 200, "SR" => 201, "ST" => 202, "SV" => 203, 
	"SY" => 204, "SZ" => 205, "TC" => 206, "TD" => 207, "TF" => 208, "TG" => 209, 
	"TH" => 210, "TJ" => 211, "TK" => 212, "TM" => 213, "TN" => 214, "TO" => 215, 
	"TL" => 216, "TR" => 217, "TT" => 218, "TV" => 219, "TW" => 220, "TZ" => 221, 
	"UA" => 222, "UG" => 223, "UM" => 224, "US" => 225, "UY" => 226, "UZ" => 227, 
	"VA" => 228, "VC" => 229, "VE" => 230, "VG" => 231, "VI" => 232, "VN" => 233,
	"VU" => 234, "WF" => 235, "WS" => 236, "YE" => 237, "YT" => 238, "RS" => 239, 
	"ZA" => 240, "ZM" => 241, "ME" => 242, "ZW" => 243, "A1" => 244, "A2" => 245, 
	"O1" => 246, "AX" => 247, "GG" => 248, "IM" => 249, "JE" => 250, "BL" => 251,
	"MF" => 252);
	
    public $GEOIP_COUNTRY_CODES = array(
	"", "AP", "EU", "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AN", "AO", "AQ",
	"AR", "AS", "AT", "AU", "AW", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH",
	"BI", "BJ", "BM", "BN", "BO", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA",
	"CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU",
	"CV", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG",
	"EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "FX", "GA", "GB",
	"GD", "GE", "GF", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT",
	"GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IN",
	"IO", "IQ", "IR", "IS", "IT", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM",
	"KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS",
	"LT", "LU", "LV", "LY", "MA", "MC", "MD", "MG", "MH", "MK", "ML", "MM", "MN",
	"MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA",
	"NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA",
	"PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY",
	"QA", "RE", "RO", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI",
	"SJ", "SK", "SL", "SM", "SN", "SO", "SR", "ST", "SV", "SY", "SZ", "TC", "TD",
	"TF", "TG", "TH", "TJ", "TK", "TM", "TN", "TO", "TL", "TR", "TT", "TV", "TW",
	"TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN",
	"VU", "WF", "WS", "YE", "YT", "RS", "ZA", "ZM", "ME", "ZW", "A1", "A2", "O1",
	"AX", "GG", "IM", "JE", "BL", "MF");

    public $GEOIP_COUNTRY_CODES3 = array(
	"","AP","EU","AND","ARE","AFG","ATG","AIA","ALB","ARM","ANT","AGO","AQ","ARG",
	"ASM","AUT","AUS","ABW","AZE","BIH","BRB","BGD","BEL","BFA","BGR","BHR","BDI",
	"BEN","BMU","BRN","BOL","BRA","BHS","BTN","BV","BWA","BLR","BLZ","CAN","CC",
	"COD","CAF","COG","CHE","CIV","COK","CHL","CMR","CHN","COL","CRI","CUB","CPV",
	"CX","CYP","CZE","DEU","DJI","DNK","DMA","DOM","DZA","ECU","EST","EGY","ESH",
	"ERI","ESP","ETH","FIN","FJI","FLK","FSM","FRO","FRA","FX","GAB","GBR","GRD",
	"GEO","GUF","GHA","GIB","GRL","GMB","GIN","GLP","GNQ","GRC","GS","GTM","GUM",
	"GNB","GUY","HKG","HM","HND","HRV","HTI","HUN","IDN","IRL","ISR","IND","IO",
	"IRQ","IRN","ISL","ITA","JAM","JOR","JPN","KEN","KGZ","KHM","KIR","COM","KNA",
	"PRK","KOR","KWT","CYM","KAZ","LAO","LBN","LCA","LIE","LKA","LBR","LSO","LTU",
	"LUX","LVA","LBY","MAR","MCO","MDA","MDG","MHL","MKD","MLI","MMR","MNG","MAC",
	"MNP","MTQ","MRT","MSR","MLT","MUS","MDV","MWI","MEX","MYS","MOZ","NAM","NCL",
	"NER","NFK","NGA","NIC","NLD","NOR","NPL","NRU","NIU","NZL","OMN","PAN","PER",
	"PYF","PNG","PHL","PAK","POL","SPM","PCN","PRI","PSE","PRT","PLW","PRY","QAT",
	"REU","ROU","RUS","RWA","SAU","SLB","SYC","SDN","SWE","SGP","SHN","SVN","SJM",
	"SVK","SLE","SMR","SEN","SOM","SUR","STP","SLV","SYR","SWZ","TCA","TCD","TF",
	"TGO","THA","TJK","TKL","TLS","TKM","TUN","TON","TUR","TTO","TUV","TWN","TZA",
	"UKR","UGA","UM","USA","URY","UZB","VAT","VCT","VEN","VGB","VIR","VNM","VUT",
	"WLF","WSM","YEM","YT","SRB","ZAF","ZMB","MNE","ZWE","A1","A2","O1",
	"ALA","GGY","IMN","JEY","BLM","MAF");
		
    public $GEOIP_COUNTRY_NAMES = array(
	"", "Asia/Pacific Region", "Europe", "Andorra", "United Arab Emirates",
	"Afghanistan", "Antigua and Barbuda", "Anguilla", "Albania", "Armenia",
	"Netherlands Antilles", "Angola", "Antarctica", "Argentina", "American Samoa",
	"Austria", "Australia", "Aruba", "Azerbaijan", "Bosnia and Herzegovina",
	"Barbados", "Bangladesh", "Belgium", "Burkina Faso", "Bulgaria", "Bahrain",
	"Burundi", "Benin", "Bermuda", "Brunei Darussalam", "Bolivia", "Brazil",
	"Bahamas", "Bhutan", "Bouvet Island", "Botswana", "Belarus", "Belize",
	"Canada", "Cocos (Keeling) Islands", "Congo, The Democratic Republic of the",
	"Central African Republic", "Congo", "Switzerland", "Cote D'Ivoire", "Cook Islands",
	"Chile", "Cameroon", "China", "Colombia", "Costa Rica", "Cuba", "Cape Verde",
	"Christmas Island", "Cyprus", "Czech Republic", "Germany", "Djibouti",
	"Denmark", "Dominica", "Dominican Republic", "Algeria", "Ecuador", "Estonia",
	"Egypt", "Western Sahara", "Eritrea", "Spain", "Ethiopia", "Finland", "Fiji",
	"Falkland Islands (Malvinas)", "Micronesia, Federated States of", "Faroe Islands",
	"France", "France, Metropolitan", "Gabon", "United Kingdom",
	"Grenada", "Georgia", "French Guiana", "Ghana", "Gibraltar", "Greenland",
	"Gambia", "Guinea", "Guadeloupe", "Equatorial Guinea", "Greece", "South Georgia and the South Sandwich Islands",
	"Guatemala", "Guam", "Guinea-Bissau",
	"Guyana", "Hong Kong", "Heard Island and McDonald Islands", "Honduras",
	"Croatia", "Haiti", "Hungary", "Indonesia", "Ireland", "Israel", "India",
	"British Indian Ocean Territory", "Iraq", "Iran, Islamic Republic of",
	"Iceland", "Italy", "Jamaica", "Jordan", "Japan", "Kenya", "Kyrgyzstan",
	"Cambodia", "Kiribati", "Comoros", "Saint Kitts and Nevis", "Korea, Democratic People's Republic of",
	"Korea, Republic of", "Kuwait", "Cayman Islands",
	"Kazakhstan", "Lao People's Democratic Republic", "Lebanon", "Saint Lucia",
	"Liechtenstein", "Sri Lanka", "Liberia", "Lesotho", "Lithuania", "Luxembourg",
	"Latvia", "Libyan Arab Jamahiriya", "Morocco", "Monaco", "Moldova, Republic of",
	"Madagascar", "Marshall Islands", "Macedonia",
	"Mali", "Myanmar", "Mongolia", "Macau", "Northern Mariana Islands",
	"Martinique", "Mauritania", "Montserrat", "Malta", "Mauritius", "Maldives",
	"Malawi", "Mexico", "Malaysia", "Mozambique", "Namibia", "New Caledonia",
	"Niger", "Norfolk Island", "Nigeria", "Nicaragua", "Netherlands", "Norway",
	"Nepal", "Nauru", "Niue", "New Zealand", "Oman", "Panama", "Peru", "French Polynesia",
	"Papua New Guinea", "Philippines", "Pakistan", "Poland", "Saint Pierre and Miquelon",
	"Pitcairn Islands", "Puerto Rico", "Palestinian Territory",
	"Portugal", "Palau", "Paraguay", "Qatar", "Reunion", "Romania",
	"Russian Federation", "Rwanda", "Saudi Arabia", "Solomon Islands",
	"Seychelles", "Sudan", "Sweden", "Singapore", "Saint Helena", "Slovenia",
	"Svalbard and Jan Mayen", "Slovakia", "Sierra Leone", "San Marino", "Senegal",
	"Somalia", "Suriname", "Sao Tome and Principe", "El Salvador", "Syrian Arab Republic",
	"Swaziland", "Turks and Caicos Islands", "Chad", "French Southern Territories",
	"Togo", "Thailand", "Tajikistan", "Tokelau", "Turkmenistan",
	"Tunisia", "Tonga", "Timor-Leste", "Turkey", "Trinidad and Tobago", "Tuvalu",
	"Taiwan", "Tanzania, United Republic of", "Ukraine",
	"Uganda", "United States Minor Outlying Islands", "United States", "Uruguay",
	"Uzbekistan", "Holy See (Vatican City State)", "Saint Vincent and the Grenadines",
	"Venezuela", "Virgin Islands, British", "Virgin Islands, U.S.",
	"Vietnam", "Vanuatu", "Wallis and Futuna", "Samoa", "Yemen", "Mayotte",
	"Serbia", "South Africa", "Zambia", "Montenegro", "Zimbabwe",
	"Anonymous Proxy","Satellite Provider","Other",
	"Aland Islands","Guernsey","Isle of Man","Jersey","Saint Barthelemy","Saint Martin"
	);

    public $GEOIP_CONTINENT_CODES = array(
	"--", "AS", "EU", "EU", "AS", "AS", "SA", "SA", "EU", "AS",
	"SA", "AF", "AN", "SA", "OC", "EU", "OC", "SA", "AS", "EU",
	"SA", "AS", "EU", "AF", "EU", "AS", "AF", "AF", "SA", "AS",
	"SA", "SA", "SA", "AS", "AF", "AF", "EU", "SA", "NA", "AS",
	"AF", "AF", "AF", "EU", "AF", "OC", "SA", "AF", "AS", "SA",
	"SA", "SA", "AF", "AS", "AS", "EU", "EU", "AF", "EU", "SA",
	"SA", "AF", "SA", "EU", "AF", "AF", "AF", "EU", "AF", "EU",
	"OC", "SA", "OC", "EU", "EU", "EU", "AF", "EU", "SA", "AS",
	"SA", "AF", "EU", "SA", "AF", "AF", "SA", "AF", "EU", "SA",
	"SA", "OC", "AF", "SA", "AS", "AF", "SA", "EU", "SA", "EU",
	"AS", "EU", "AS", "AS", "AS", "AS", "AS", "EU", "EU", "SA",
	"AS", "AS", "AF", "AS", "AS", "OC", "AF", "SA", "AS", "AS",
	"AS", "SA", "AS", "AS", "AS", "SA", "EU", "AS", "AF", "AF",
	"EU", "EU", "EU", "AF", "AF", "EU", "EU", "AF", "OC", "EU",
	"AF", "AS", "AS", "AS", "OC", "SA", "AF", "SA", "EU", "AF",
	"AS", "AF", "NA", "AS", "AF", "AF", "OC", "AF", "OC", "AF",
	"SA", "EU", "EU", "AS", "OC", "OC", "OC", "AS", "SA", "SA",
	"OC", "OC", "AS", "AS", "EU", "SA", "OC", "SA", "AS", "EU",
	"OC", "SA", "AS", "AF", "EU", "AS", "AF", "AS", "OC", "AF",
	"AF", "EU", "AS", "AF", "EU", "EU", "EU", "AF", "EU", "AF",
	"AF", "SA", "AF", "SA", "AS", "AF", "SA", "AF", "AF", "AF",
	"AS", "AS", "OC", "AS", "AF", "OC", "AS", "EU", "SA", "OC",
	"AS", "AF", "EU", "AF", "OC", "NA", "SA", "AS", "EU", "SA",
	"SA", "SA", "SA", "AS", "OC", "OC", "OC", "AS", "AF", "EU",
	"AF", "AF", "EU", "AF", "--", "--", "--", "EU", "EU", "EU",
	"EU", "SA", "SA" );
    
}

function geoip_load_shared_mem ($file) {

  $fp = fopen($file, "rb");
  if (!$fp) {
    print "error opening $file: $php_errormsg\n";
    exit;
  }
  $s_array = fstat($fp);
  $size = $s_array['size'];
  if ($shmid = @shmop_open (GEOIP_SHM_KEY, "w", 0, 0)) {
    shmop_delete ($shmid);
    shmop_close ($shmid);
  }
  $shmid = shmop_open (GEOIP_SHM_KEY, "c", 0644, $size);
  shmop_write ($shmid, fread($fp, $size), 0);
  shmop_close ($shmid);
}

function _setup_segments($gi){
  $gi->databaseType = GEOIP_COUNTRY_EDITION;
  $gi->record_length = STANDARD_RECORD_LENGTH;
  if ($gi->flags & GEOIP_SHARED_MEMORY) {
    $offset = @shmop_size ($gi->shmid) - 3;
    for ($i = 0; $i < STRUCTURE_INFO_MAX_SIZE; $i++) {
        $delim = @shmop_read ($gi->shmid, $offset, 3);
        $offset += 3;
        if ($delim == (chr(255).chr(255).chr(255))) {
            $gi->databaseType = ord(@shmop_read ($gi->shmid, $offset, 1));
            $offset++;

            if ($gi->databaseType == GEOIP_REGION_EDITION_REV0){
                $gi->databaseSegments = GEOIP_STATE_BEGIN_REV0;
            } else if ($gi->databaseType == GEOIP_REGION_EDITION_REV1){
                $gi->databaseSegments = GEOIP_STATE_BEGIN_REV1;
	    } else if (($gi->databaseType == GEOIP_CITY_EDITION_REV0)||
                     ($gi->databaseType == GEOIP_CITY_EDITION_REV1) 
                    || ($gi->databaseType == GEOIP_ORG_EDITION)
		    || ($gi->databaseType == GEOIP_ISP_EDITION)
		    || ($gi->databaseType == GEOIP_ASNUM_EDITION)){
                $gi->databaseSegments = 0;
                $buf = @shmop_read ($gi->shmid, $offset, SEGMENT_RECORD_LENGTH);
                for ($j = 0;$j < SEGMENT_RECORD_LENGTH;$j++){
                    $gi->databaseSegments += (ord($buf[$j]) << ($j * 8));
                }
	            if (($gi->databaseType == GEOIP_ORG_EDITION)||
			($gi->databaseType == GEOIP_ISP_EDITION)) {
	                $gi->record_length = ORG_RECORD_LENGTH;
                }
            }
            break;
        } else {
            $offset -= 4;
        }
    }
    if (($gi->databaseType == GEOIP_COUNTRY_EDITION)||
        ($gi->databaseType == GEOIP_PROXY_EDITION)||
        ($gi->databaseType == GEOIP_NETSPEED_EDITION)){
        $gi->databaseSegments = GEOIP_COUNTRY_BEGIN;
    }
  } else {
    $filepos = ftell($gi->filehandle);
    fseek($gi->filehandle, -3, SEEK_END);
    for ($i = 0; $i < STRUCTURE_INFO_MAX_SIZE; $i++) {
        $delim = fread($gi->filehandle,3);
        if ($delim == (chr(255).chr(255).chr(255))){
        $gi->databaseType = ord(fread($gi->filehandle,1));
        if ($gi->databaseType == GEOIP_REGION_EDITION_REV0){
            $gi->databaseSegments = GEOIP_STATE_BEGIN_REV0;
        }
        else if ($gi->databaseType == GEOIP_REGION_EDITION_REV1){
	    $gi->databaseSegments = GEOIP_STATE_BEGIN_REV1;
                }  else if (($gi->databaseType == GEOIP_CITY_EDITION_REV0) ||
                 ($gi->databaseType == GEOIP_CITY_EDITION_REV1) || 
                 ($gi->databaseType == GEOIP_ORG_EDITION) || 
		 ($gi->databaseType == GEOIP_ISP_EDITION) || 
                 ($gi->databaseType == GEOIP_ASNUM_EDITION)){
            $gi->databaseSegments = 0;
            $buf = fread($gi->filehandle,SEGMENT_RECORD_LENGTH);
            for ($j = 0;$j < SEGMENT_RECORD_LENGTH;$j++){
            $gi->databaseSegments += (ord($buf[$j]) << ($j * 8));
            }
	    if ($gi->databaseType == GEOIP_ORG_EDITION ||
		$gi->databaseType == GEOIP_ISP_EDITION) {
	    $gi->record_length = ORG_RECORD_LENGTH;
            }
        }
        break;
        } else {
        fseek($gi->filehandle, -4, SEEK_CUR);
        }
    }
    if (($gi->databaseType == GEOIP_COUNTRY_EDITION)||
        ($gi->databaseType == GEOIP_PROXY_EDITION)||
        ($gi->databaseType == GEOIP_NETSPEED_EDITION)){
         $gi->databaseSegments = GEOIP_COUNTRY_BEGIN;
    }
    fseek($gi->filehandle,$filepos,SEEK_SET);
  }
  return $gi;
}

function geoip_open($filename, $flags) {
  $gi = new GeoIP;
  $gi->flags = $flags;
  if ($gi->flags & GEOIP_SHARED_MEMORY) {
    $gi->shmid = @shmop_open (GEOIP_SHM_KEY, "a", 0, 0);
    } else {
    $gi->filehandle = fopen($filename,"rb") or die( "Can not open $filename\n" );
    if ($gi->flags & GEOIP_MEMORY_CACHE) {
        $s_array = fstat($gi->filehandle);
        $gi->memory_buffer = fread($gi->filehandle, $s_array['size']);
    }
  }

  $gi = _setup_segments($gi);
  return $gi;
}

function geoip_close($gi) {
  if ($gi->flags & GEOIP_SHARED_MEMORY) {
    return true;
  }

  return fclose($gi->filehandle);
}

function geoip_country_id_by_name($gi, $name) {
  $addr = gethostbyname($name);
  if (!$addr || $addr == $name) {
    return false;
  }
  return geoip_country_id_by_addr($gi, $addr);
}

function geoip_country_code_by_name($gi, $name) {
  $country_id = geoip_country_id_by_name($gi,$name);
  if ($country_id !== false) {
        return $gi->GEOIP_COUNTRY_CODES[$country_id];
  }
  return false;
}

function geoip_country_name_by_name($gi, $name) {
  $country_id = geoip_country_id_by_name($gi,$name);
  if ($country_id !== false) {
        return $gi->GEOIP_COUNTRY_NAMES[$country_id];
  }
  return false;
}

function geoip_country_id_by_addr($gi, $addr) {
  $ipnum = ip2long($addr);
  return _geoip_seek_country($gi, $ipnum) - GEOIP_COUNTRY_BEGIN;
}

function geoip_country_code_by_addr($gi, $addr) {
  if ($gi->databaseType == GEOIP_CITY_EDITION_REV1) {
    $record = geoip_record_by_addr($gi,$addr);
    if ( $record !== false ) {
      return $record->country_code;
    }
  } else {
    $country_id = geoip_country_id_by_addr($gi,$addr);
    if ($country_id !== false) {
      return $gi->GEOIP_COUNTRY_CODES[$country_id];
    }
  }
  return false;
}

function geoip_country_name_by_addr($gi, $addr) {
  if ($gi->databaseType == GEOIP_CITY_EDITION_REV1) {
    $record = geoip_record_by_addr($gi,$addr);
    return $record->country_name;
  } else {
    $country_id = geoip_country_id_by_addr($gi,$addr);
    if ($country_id !== false) {
      return $gi->GEOIP_COUNTRY_NAMES[$country_id];
    }
  }
  return false;
}

function _geoip_seek_country($gi, $ipnum) {
  $offset = 0;
  for ($depth = 31; $depth >= 0; --$depth) {
    if ($gi->flags & GEOIP_MEMORY_CACHE) {
      // workaround php's broken substr, strpos, etc handling with
      // mbstring.func_overload and mbstring.internal_encoding
      $enc = mb_internal_encoding();
       mb_internal_encoding('ISO-8859-1'); 

      $buf = substr($gi->memory_buffer,
                            2 * $gi->record_length * $offset,
                            2 * $gi->record_length);

      mb_internal_encoding($enc);
    } elseif ($gi->flags & GEOIP_SHARED_MEMORY) {
      $buf = @shmop_read ($gi->shmid, 
                            2 * $gi->record_length * $offset,
                            2 * $gi->record_length );
        } else {
      fseek($gi->filehandle, 2 * $gi->record_length * $offset, SEEK_SET) == 0
        or die("fseek failed");
      $buf = fread($gi->filehandle, 2 * $gi->record_length);
    }
    $x = array(0,0);
    for ($i = 0; $i < 2; ++$i) {
      for ($j = 0; $j < $gi->record_length; ++$j) {
        $x[$i] += ord($buf[$gi->record_length * $i + $j]) << ($j * 8);
      }
    }
    if ($ipnum & (1 << $depth)) {
      if ($x[1] >= $gi->databaseSegments) {
        return $x[1];
      }
      $offset = $x[1];
        } else {
      if ($x[0] >= $gi->databaseSegments) {
        return $x[0];
      }
      $offset = $x[0];
    }
  }
  trigger_error("error traversing database - perhaps it is corrupt?", E_USER_ERROR);
  return false;
}

function _get_org($gi,$ipnum){
  $seek_org = _geoip_seek_country($gi,$ipnum);
  if ($seek_org == $gi->databaseSegments) {
    return NULL;
  }
  $record_pointer = $seek_org + (2 * $gi->record_length - 1) * $gi->databaseSegments;
  if ($gi->flags & GEOIP_SHARED_MEMORY) {
    $org_buf = @shmop_read ($gi->shmid, $record_pointer, MAX_ORG_RECORD_LENGTH);
    } else {
    fseek($gi->filehandle, $record_pointer, SEEK_SET);
    $org_buf = fread($gi->filehandle,MAX_ORG_RECORD_LENGTH);
  }
  // workaround php's broken substr, strpos, etc handling with
  // mbstring.func_overload and mbstring.internal_encoding
  $enc = mb_internal_encoding();
  mb_internal_encoding('ISO-8859-1'); 
  $org_buf = substr($org_buf, 0, strpos($org_buf, 0));
  mb_internal_encoding($enc);
  return $org_buf;
}

function geoip_org_by_addr ($gi,$addr) {
  if ($addr == NULL) {
    return 0;
  }
  $ipnum = ip2long($addr);
  return _get_org($gi, $ipnum);
}

function _get_region($gi,$ipnum){
  if ($gi->databaseType == GEOIP_REGION_EDITION_REV0){
    $seek_region = _geoip_seek_country($gi,$ipnum) - GEOIP_STATE_BEGIN_REV0;
    if ($seek_region >= 1000){
      $country_code = "US";
      $region = chr(($seek_region - 1000)/26 + 65) . chr(($seek_region - 1000)%26 + 65);
    } else {
            $country_code = $gi->GEOIP_COUNTRY_CODES[$seek_region];
      $region = "";
    }
  return array ($country_code,$region);
    }  else if ($gi->databaseType == GEOIP_REGION_EDITION_REV1) {
    $seek_region = _geoip_seek_country($gi,$ipnum) - GEOIP_STATE_BEGIN_REV1;
    //print $seek_region;
    if ($seek_region < US_OFFSET){
      $country_code = "";
      $region = "";  
        } else if ($seek_region < CANADA_OFFSET) {
      $country_code = "US";
      $region = chr(($seek_region - US_OFFSET)/26 + 65) . chr(($seek_region - US_OFFSET)%26 + 65);
        } else if ($seek_region < WORLD_OFFSET) {
      $country_code = "CA";
      $region = chr(($seek_region - CANADA_OFFSET)/26 + 65) . chr(($seek_region - CANADA_OFFSET)%26 + 65);
    } else {
            $country_code = $gi->GEOIP_COUNTRY_CODES[($seek_region - WORLD_OFFSET) / FIPS_RANGE];
      $region = "";
    }
  return array ($country_code,$region);
  }
}

function geoip_region_by_addr ($gi,$addr) {
  if ($addr == NULL) {
    return 0;
  }
  $ipnum = ip2long($addr);
  return _get_region($gi, $ipnum);
}

function getdnsattributes ($l,$ip){
  $r = new Net_DNS_Resolver();
  $r->nameservers = array("ws1.maxmind.com");
  $p = $r->search($l."." . $ip .".s.maxmind.com","TXT","IN");
  $str = is_object($p->answer[0])?$p->answer[0]->string():'';
  ereg("\"(.*)\"",$str,$regs);
  $str = $regs[1];
  return $str;
}

function get_time_zone($country,$region) {
  $timezone = NULL;
  
  switch ($country) { 
	case "US":
		switch ($region) { 
	  case "AL":
		  $timezone = "America/Chicago";
		  break; 
	  case "AK":
		  $timezone = "America/Anchorage";
		  break; 
	  case "AZ":
		  $timezone = "America/Phoenix";
		  break; 
	  case "AR":
		  $timezone = "America/Chicago";
		  break; 
	  case "CA":
		  $timezone = "America/Los_Angeles";
		  break; 
	  case "CO":
		  $timezone = "America/Denver";
		  break; 
	  case "CT":
		  $timezone = "America/New_York";
		  break; 
	  case "DE":
		  $timezone = "America/New_York";
		  break; 
	  case "DC":
		  $timezone = "America/New_York";
		  break; 
	  case "FL":
		  $timezone = "America/New_York";
		  break; 
	  case "GA":
		  $timezone = "America/New_York";
		  break; 
	  case "HI":
		  $timezone = "Pacific/Honolulu";
		  break; 
	  case "ID":
		  $timezone = "America/Denver";
		  break; 
	  case "IL":
		  $timezone = "America/Chicago";
		  break; 
	  case "IN":
		  $timezone = "America/Indianapolis";
		  break; 
	  case "IA":
		  $timezone = "America/Chicago";
		  break; 
	  case "KS":
		  $timezone = "America/Chicago";
		  break; 
	  case "KY":
		  $timezone = "America/New_York";
		  break; 
	  case "LA":
		  $timezone = "America/Chicago";
		  break; 
	  case "ME":
		  $timezone = "America/New_York";
		  break; 
	  case "MD":
		  $timezone = "America/New_York";
		  break; 
	  case "MA":
		  $timezone = "America/New_York";
		  break; 
	  case "MI":
		  $timezone = "America/New_York";
		  break; 
	  case "MN":
		  $timezone = "America/Chicago";
		  break; 
	  case "MS":
		  $timezone = "America/Chicago";
		  break; 
	  case "MO":
		  $timezone = "America/Chicago";
		  break; 
	  case "MT":
		  $timezone = "America/Denver";
		  break; 
	  case "NE":
		  $timezone = "America/Chicago";
		  break; 
	  case "NV":
		  $timezone = "America/Los_Angeles";
		  break; 
	  case "NH":
		  $timezone = "America/New_York";
		  break; 
	  case "NJ":
		  $timezone = "America/New_York";
		  break; 
	  case "NM":
		  $timezone = "America/Denver";
		  break; 
	  case "NY":
		  $timezone = "America/New_York";
		  break; 
	  case "NC":
		  $timezone = "America/New_York";
		  break; 
	  case "ND":
		  $timezone = "America/Chicago";
		  break; 
	  case "OH":
		  $timezone = "America/New_York";
		  break; 
	  case "OK":
		  $timezone = "America/Chicago";
		  break; 
	  case "OR":
		  $timezone = "America/Los_Angeles";
		  break; 
	  case "PA":
		  $timezone = "America/New_York";
		  break; 
	  case "RI":
		  $timezone = "America/New_York";
		  break; 
	  case "SC":
		  $timezone = "America/New_York";
		  break; 
	  case "SD":
		  $timezone = "America/Chicago";
		  break; 
	  case "TN":
		  $timezone = "America/Chicago";
		  break; 
	  case "TX":
		  $timezone = "America/Chicago";
		  break; 
	  case "UT":
		  $timezone = "America/Denver";
		  break; 
	  case "VT":
		  $timezone = "America/New_York";
		  break; 
	  case "VA":
		  $timezone = "America/New_York";
		  break; 
	  case "WA":
		  $timezone = "America/Los_Angeles";
		  break; 
	  case "WV":
		  $timezone = "America/New_York";
		  break; 
	  case "WI":
		  $timezone = "America/Chicago";
		  break; 
	  case "WY":
		  $timezone = "America/Denver";
		  break; 
	  } 
	  break; 
	case "CA":
		switch ($region) { 
	  case "AB":
		  $timezone = "America/Edmonton";
		  break; 
	  case "BC":
		  $timezone = "America/Vancouver";
		  break; 
	  case "MB":
		  $timezone = "America/Winnipeg";
		  break; 
	  case "NB":
		  $timezone = "America/Halifax";
		  break; 
	  case "NL":
		  $timezone = "America/St_Johns";
		  break; 
	  case "NT":
		  $timezone = "America/Yellowknife";
		  break; 
	  case "NS":
		  $timezone = "America/Halifax";
		  break; 
	  case "NU":
		  $timezone = "America/Rankin_Inlet";
		  break; 
	  case "ON":
		  $timezone = "America/Rainy_River";
		  break; 
	  case "PE":
		  $timezone = "America/Halifax";
		  break; 
	  case "QC":
		  $timezone = "America/Montreal";
		  break; 
	  case "SK":
		  $timezone = "America/Regina";
		  break; 
	  case "YT":
		  $timezone = "America/Whitehorse";
		  break; 
	  } 
	  break; 
	case "AU":
		switch ($region) { 
	  case "01":
		  $timezone = "Australia/Canberra";
		  break; 
	  case "02":
		  $timezone = "Australia/NSW";
		  break; 
	  case "03":
		  $timezone = "Australia/North";
		  break; 
	  case "04":
		  $timezone = "Australia/Queensland";
		  break; 
	  case "05":
		  $timezone = "Australia/South";
		  break; 
	  case "06":
		  $timezone = "Australia/Tasmania";
		  break; 
	  case "07":
		  $timezone = "Australia/Victoria";
		  break; 
	  case "08":
		  $timezone = "Australia/West";
		  break; 
	  } 
	  break; 
	case "AS":
		$timezone = "US/Samoa";
		break; 
	case "CI":
		$timezone = "Africa/Abidjan";
		break; 
	case "GH":
		$timezone = "Africa/Accra";
		break; 
	case "DZ":
		$timezone = "Africa/Algiers";
		break; 
	case "ER":
		$timezone = "Africa/Asmera";
		break; 
	case "ML":
		$timezone = "Africa/Bamako";
		break; 
	case "CF":
		$timezone = "Africa/Bangui";
		break; 
	case "GM":
		$timezone = "Africa/Banjul";
		break; 
	case "GW":
		$timezone = "Africa/Bissau";
		break; 
	case "CG":
		$timezone = "Africa/Brazzaville";
		break; 
	case "BI":
		$timezone = "Africa/Bujumbura";
		break; 
	case "EG":
		$timezone = "Africa/Cairo";
		break; 
	case "MA":
		$timezone = "Africa/Casablanca";
		break; 
	case "GN":
		$timezone = "Africa/Conakry";
		break; 
	case "SN":
		$timezone = "Africa/Dakar";
		break; 
	case "DJ":
		$timezone = "Africa/Djibouti";
		break; 
	case "SL":
		$timezone = "Africa/Freetown";
		break; 
	case "BW":
		$timezone = "Africa/Gaborone";
		break; 
	case "ZW":
		$timezone = "Africa/Harare";
		break; 
	case "ZA":
		$timezone = "Africa/Johannesburg";
		break; 
	case "UG":
		$timezone = "Africa/Kampala";
		break; 
	case "SD":
		$timezone = "Africa/Khartoum";
		break; 
	case "RW":
		$timezone = "Africa/Kigali";
		break; 
	case "NG":
		$timezone = "Africa/Lagos";
		break; 
	case "GA":
		$timezone = "Africa/Libreville";
		break; 
	case "TG":
		$timezone = "Africa/Lome";
		break; 
	case "AO":
		$timezone = "Africa/Luanda";
		break; 
	case "ZM":
		$timezone = "Africa/Lusaka";
		break; 
	case "GQ":
		$timezone = "Africa/Malabo";
		break; 
	case "MZ":
		$timezone = "Africa/Maputo";
		break; 
	case "LS":
		$timezone = "Africa/Maseru";
		break; 
	case "SZ":
		$timezone = "Africa/Mbabane";
		break; 
	case "SO":
		$timezone = "Africa/Mogadishu";
		break; 
	case "LR":
		$timezone = "Africa/Monrovia";
		break; 
	case "KE":
		$timezone = "Africa/Nairobi";
		break; 
	case "TD":
		$timezone = "Africa/Ndjamena";
		break; 
	case "NE":
		$timezone = "Africa/Niamey";
		break; 
	case "MR":
		$timezone = "Africa/Nouakchott";
		break; 
	case "BF":
		$timezone = "Africa/Ouagadougou";
		break; 
	case "ST":
		$timezone = "Africa/Sao_Tome";
		break; 
	case "LY":
		$timezone = "Africa/Tripoli";
		break; 
	case "TN":
		$timezone = "Africa/Tunis";
		break; 
	case "AI":
		$timezone = "America/Anguilla";
		break; 
	case "AG":
		$timezone = "America/Antigua";
		break; 
	case "AW":
		$timezone = "America/Aruba";
		break; 
	case "BB":
		$timezone = "America/Barbados";
		break; 
	case "BZ":
		$timezone = "America/Belize";
		break; 
	case "CO":
		$timezone = "America/Bogota";
		break; 
	case "VE":
		$timezone = "America/Caracas";
		break; 
	case "KY":
		$timezone = "America/Cayman";
		break; 
	case "MX":
		$timezone = "America/Chihuahua";
		break; 
	case "CR":
		$timezone = "America/Costa_Rica";
		break; 
	case "DM":
		$timezone = "America/Dominica";
		break; 
	case "SV":
		$timezone = "America/El_Salvador";
		break; 
	case "GD":
		$timezone = "America/Grenada";
		break; 
	case "FR":
		$timezone = "Europe/Paris";
		break; 
	case "GP":
		$timezone = "America/Guadeloupe";
		break; 
	case "GT":
		$timezone = "America/Guatemala";
		break; 
	case "EC":
		$timezone = "America/Guayaquil";
		break; 
	case "GY":
		$timezone = "America/Guyana";
		break; 
	case "CU":
		$timezone = "America/Havana";
		break; 
	case "JM":
		$timezone = "America/Jamaica";
		break; 
	case "BO":
		$timezone = "America/La_Paz";
		break; 
	case "PE":
		$timezone = "America/Lima";
		break; 
	case "NI":
		$timezone = "America/Managua";
		break; 
	case "MQ":
		$timezone = "America/Martinique";
		break; 
	case "AR":
		$timezone = "America/Mendoza";
		break; 
	case "UY":
		$timezone = "America/Montevideo";
		break; 
	case "MS":
		$timezone = "America/Montserrat";
		break; 
	case "BS":
		$timezone = "America/Nassau";
		break; 
	case "PA":
		$timezone = "America/Panama";
		break; 
	case "SR":
		$timezone = "America/Paramaribo";
		break; 
	case "PR":
		$timezone = "America/Puerto_Rico";
		break; 
	case "KN":
		$timezone = "America/St_Kitts";
		break; 
	case "LC":
		$timezone = "America/St_Lucia";
		break; 
	case "VC":
		$timezone = "America/St_Vincent";
		break; 
	case "HN":
		$timezone = "America/Tegucigalpa";
		break; 
	case "YE":
		$timezone = "Asia/Aden";
		break; 
	case "KZ":
		$timezone = "Asia/Almaty";
		break; 
	case "JO":
		$timezone = "Asia/Amman";
		break; 
	case "TM":
		$timezone = "Asia/Ashgabat";
		break; 
	case "IQ":
		$timezone = "Asia/Baghdad";
		break; 
	case "BH":
		$timezone = "Asia/Bahrain";
		break; 
	case "AZ":
		$timezone = "Asia/Baku";
		break; 
	case "TH":
		$timezone = "Asia/Bangkok";
		break; 
	case "LB":
		$timezone = "Asia/Beirut";
		break; 
	case "KG":
		$timezone = "Asia/Bishkek";
		break; 
	case "BN":
		$timezone = "Asia/Brunei";
		break; 
	case "IN":
		$timezone = "Asia/Calcutta";
		break; 
	case "MN":
		$timezone = "Asia/Choibalsan";
		break; 
	case "CN":
		$timezone = "Asia/Chongqing";
		break; 
	case "LK":
		$timezone = "Asia/Colombo";
		break; 
	case "BD":
		$timezone = "Asia/Dhaka";
		break; 
	case "AE":
		$timezone = "Asia/Dubai";
		break; 
	case "TJ":
		$timezone = "Asia/Dushanbe";
		break; 
	case "HK":
		$timezone = "Asia/Hong_Kong";
		break; 
	case "TR":
		$timezone = "Asia/Istanbul";
		break; 
	case "ID":
		$timezone = "Asia/Jakarta";
		break; 
	case "IL":
		$timezone = "Asia/Jerusalem";
		break; 
	case "AF":
		$timezone = "Asia/Kabul";
		break; 
	case "PK":
		$timezone = "Asia/Karachi";
		break; 
	case "NP":
		$timezone = "Asia/Katmandu";
		break; 
	case "KW":
		$timezone = "Asia/Kuwait";
		break; 
	case "MO":
		$timezone = "Asia/Macao";
		break; 
	case "PH":
		$timezone = "Asia/Manila";
		break; 
	case "OM":
		$timezone = "Asia/Muscat";
		break; 
	case "CY":
		$timezone = "Asia/Nicosia";
		break; 
	case "KP":
		$timezone = "Asia/Pyongyang";
		break; 
	case "QA":
		$timezone = "Asia/Qatar";
		break; 
	case "MM":
		$timezone = "Asia/Rangoon";
		break; 
	case "SA":
		$timezone = "Asia/Riyadh";
		break; 
	case "KR":
		$timezone = "Asia/Seoul";
		break; 
	case "SG":
		$timezone = "Asia/Singapore";
		break; 
	case "TW":
		$timezone = "Asia/Taipei";
		break; 
	case "UZ":
		$timezone = "Asia/Tashkent";
		break; 
	case "GE":
		$timezone = "Asia/Tbilisi";
		break; 
	case "BT":
		$timezone = "Asia/Thimphu";
		break; 
	case "JP":
		$timezone = "Asia/Tokyo";
		break; 
	case "LA":
		$timezone = "Asia/Vientiane";
		break; 
	case "AM":
		$timezone = "Asia/Yerevan";
		break; 
	case "PT":
		$timezone = "Atlantic/Azores";
		break; 
	case "BM":
		$timezone = "Atlantic/Bermuda";
		break; 
	case "CV":
		$timezone = "Atlantic/Cape_Verde";
		break; 
	case "FO":
		$timezone = "Atlantic/Faeroe";
		break; 
	case "IS":
		$timezone = "Atlantic/Reykjavik";
		break; 
	case "GS":
		$timezone = "Atlantic/South_Georgia";
		break; 
	case "SH":
		$timezone = "Atlantic/St_Helena";
		break; 
	case "BR":
		$timezone = "Brazil/Acre";
		break; 
	case "CL":
		$timezone = "Chile/Continental";
		break; 
	case "NL":
		$timezone = "Europe/Amsterdam";
		break; 
	case "AD":
		$timezone = "Europe/Andorra";
		break; 
	case "GR":
		$timezone = "Europe/Athens";
		break; 
	case "YU":
		$timezone = "Europe/Belgrade";
		break; 
	case "DE":
		$timezone = "Europe/Berlin";
		break; 
	case "SK":
		$timezone = "Europe/Bratislava";
		break; 
	case "BE":
		$timezone = "Europe/Brussels";
		break; 
	case "RO":
		$timezone = "Europe/Bucharest";
		break; 
	case "HU":
		$timezone = "Europe/Budapest";
		break; 
	case "DK":
		$timezone = "Europe/Copenhagen";
		break; 
	case "IE":
		$timezone = "Europe/Dublin";
		break; 
	case "GI":
		$timezone = "Europe/Gibraltar";
		break; 
	case "FI":
		$timezone = "Europe/Helsinki";
		break; 
	case "UA":
		$timezone = "Europe/Kiev";
		break; 
	case "SI":
		$timezone = "Europe/Ljubljana";
		break; 
	case "GB":
		$timezone = "Europe/London";
		break; 
	case "LU":
		$timezone = "Europe/Luxembourg";
		break; 
	case "ES":
		$timezone = "Europe/Madrid";
		break; 
	case "MT":
		$timezone = "Europe/Malta";
		break; 
	case "BY":
		$timezone = "Europe/Minsk";
		break; 
	case "MC":
		$timezone = "Europe/Monaco";
		break; 
	case "RU":
		$timezone = "Europe/Moscow";
		break; 
	case "NO":
		$timezone = "Europe/Oslo";
		break; 
	case "CZ":
		$timezone = "Europe/Prague";
		break; 
	case "LV":
		$timezone = "Europe/Riga";
		break; 
	case "IT":
		$timezone = "Europe/Rome";
		break; 
	case "SM":
		$timezone = "Europe/San_Marino";
		break; 
	case "BA":
		$timezone = "Europe/Sarajevo";
		break; 
	case "MK":
		$timezone = "Europe/Skopje";
		break; 
	case "BG":
		$timezone = "Europe/Sofia";
		break; 
	case "SE":
		$timezone = "Europe/Stockholm";
		break; 
	case "EE":
		$timezone = "Europe/Tallinn";
		break; 
	case "AL":
		$timezone = "Europe/Tirane";
		break; 
	case "LI":
		$timezone = "Europe/Vaduz";
		break; 
	case "VA":
		$timezone = "Europe/Vatican";
		break; 
	case "AT":
		$timezone = "Europe/Vienna";
		break; 
	case "LT":
		$timezone = "Europe/Vilnius";
		break; 
	case "PL":
		$timezone = "Europe/Warsaw";
		break; 
	case "HR":
		$timezone = "Europe/Zagreb";
		break; 
	case "IR":
		$timezone = "Asia/Tehran";
		break; 
	case "NZ":
		$timezone = "Pacific/Auckland";
		break; 
	case "MG":
		$timezone = "Indian/Antananarivo";
		break; 
	case "CX":
		$timezone = "Indian/Christmas";
		break; 
	case "CC":
		$timezone = "Indian/Cocos";
		break; 
	case "KM":
		$timezone = "Indian/Comoro";
		break; 
	case "MV":
		$timezone = "Indian/Maldives";
		break; 
	case "MU":
		$timezone = "Indian/Mauritius";
		break; 
	case "YT":
		$timezone = "Indian/Mayotte";
		break; 
	case "RE":
		$timezone = "Indian/Reunion";
		break; 
	case "FJ":
		$timezone = "Pacific/Fiji";
		break; 
	case "TV":
		$timezone = "Pacific/Funafuti";
		break; 
	case "GU":
		$timezone = "Pacific/Guam";
		break; 
	case "NR":
		$timezone = "Pacific/Nauru";
		break; 
	case "NU":
		$timezone = "Pacific/Niue";
		break; 
	case "NF":
		$timezone = "Pacific/Norfolk";
		break; 
	case "PW":
		$timezone = "Pacific/Palau";
		break; 
	case "PN":
		$timezone = "Pacific/Pitcairn";
		break; 
	case "CK":
		$timezone = "Pacific/Rarotonga";
		break; 
	case "WS":
		$timezone = "Pacific/Samoa";
		break; 
	case "KI":
		$timezone = "Pacific/Tarawa";
		break; 
	case "TO":
		$timezone = "Pacific/Tongatapu";
		break; 
	case "WF":
		$timezone = "Pacific/Wallis";
		break; 
	case "TZ":
		$timezone = "Africa/Dar_es_Salaam";
		break; 
	case "VN":
		$timezone = "Asia/Phnom_Penh";
		break; 
	case "KH":
		$timezone = "Asia/Phnom_Penh";
		break; 
	case "CM":
		$timezone = "Africa/Lagos";
		break; 
	case "DO":
		$timezone = "America/Santo_Domingo";
		break; 
	case "TL":
		$timezone = "Asia/Jakarta";
		break; 
	case "ET":
		$timezone = "Africa/Addis_Ababa";
		break; 
	case "FX":
		$timezone = "Europe/Paris";
		break; 
	case "GL":
		$timezone = "America/Godthab";
		break; 
	case "HT":
		$timezone = "America/Port-au-Prince";
		break; 
	case "CH":
		$timezone = "Europe/Zurich";
		break; 
	case "AN":
		$timezone = "America/Curacao";
		break; 
	case "BJ":
		$timezone = "Africa/Porto-Novo";
		break; 
	case "EH":
		$timezone = "Africa/El_Aaiun";
		break; 
	case "FK":
		$timezone = "Atlantic/Stanley";
		break; 
	case "GF":
		$timezone = "America/Cayenne";
		break; 
	case "IO":
		$timezone = "Indian/Chagos";
		break; 
	case "MD":
		$timezone = "Europe/Chisinau";
		break; 
	case "MP":
		$timezone = "Pacific/Saipan";
		break; 
	case "MW":
		$timezone = "Africa/Blantyre";
		break; 
	case "NA":
		$timezone = "Africa/Windhoek";
		break; 
	case "NC":
		$timezone = "Pacific/Noumea";
		break; 
	case "PG":
		$timezone = "Pacific/Port_Moresby";
		break; 
	case "PM":
		$timezone = "America/Miquelon";
		break; 
	case "PS":
		$timezone = "Asia/Gaza";
		break; 
	case "PY":
		$timezone = "America/Asuncion";
		break; 
	case "SB":
		$timezone = "Pacific/Guadalcanal";
		break; 
	case "SC":
		$timezone = "Indian/Mahe";
		break; 
	case "SJ":
		$timezone = "Arctic/Longyearbyen";
		break; 
	case "SY":
		$timezone = "Asia/Damascus";
		break; 
	case "TC":
		$timezone = "America/Grand_Turk";
		break; 
	case "TF":
		$timezone = "Indian/Kerguelen";
		break; 
	case "TK":
		$timezone = "Pacific/Fakaofo";
		break; 
	case "TT":
		$timezone = "America/Port_of_Spain";
		break; 
	case "VG":
		$timezone = "America/Tortola";
		break; 
	case "VI":
		$timezone = "America/St_Thomas";
		break; 
	case "VU":
		$timezone = "Pacific/Efate";
		break; 
	case "RS":
		$timezone = "Europe/Belgrade";
		break; 
	case "ME":
		$timezone = "Europe/Podgorica";
		break; 
	case "AX":
		$timezone = "Europe/Mariehamn";
		break; 
	case "GG":
		$timezone = "Europe/Guernsey";
		break; 
	case "IM":
		$timezone = "Europe/Isle_of_Man";
		break; 
	case "JE":
		$timezone = "Europe/Jersey";
		break; 
	case "BL":
		$timezone = "America/St_Barthelemy";
		break; 
	case "MF":
		$timezone = "America/Marigot";
		break; 
  } 
  return $timezone; 
} 

?>

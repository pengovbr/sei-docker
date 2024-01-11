<?

/** Fun��es utilit�rias para a manipula��o de certificados digitais */

class InfraCertificadoDigital
{

  private static function gerarXmlCertificado($dados)
  {
    // Recebe array com dados de um certificado e gera o xml com estes dados.
    // As chaves formam os tags e os dados  o conteudo do tag ....
    $aux_xml = "<certificado>";
    if(is_array($dados))
    {
      foreach($dados as $K => $valor)
      {
        $aux_x = substr($K,0,1);
        if(is_numeric($aux_x)) $K = 'oid-' . $K;
        $K = trim($K);
        if(!is_array($valor))
        {
          $aux_xml .= '<' . $K . '>'.$valor.'</' . $K . '>';
        }
        else
        {
          $aux_xml .= '<' . $K . '>';
          foreach($valor as $KX => $valorx)
          {
            //$KX = trim($KX);
            if(is_int($KX))  $KX = 'D' . $KX;
            $KX = trim($KX);
            $aux_xml .= '<' . $KX . '>'.$valorx.'</' . $KX . '>';
          }
          $aux_xml .= '</' . $K . '>';
        }
      }
    }
    $aux_xml .= "</certificado>";
    return  $aux_xml;
  }

  private static function gerarDataHora ($valor)
  {
    $year  = substr($valor, 0, 2);
    $month = substr($valor, 2, 2);
    $day   = substr($valor, 4, 2);
    $hour  = substr($valor, 6, 2);
    $min   = substr($valor, 8, 2);
    $sec   = substr($valor, 10, 2);
    return gmdate('YmdHis',gmmktime($hour, $min, $sec, $month, $day, $year));
  }

  private static function imprimirHex($value)
  {
    $hex = '';
    $tab_val = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
    for($A=0;$A<strlen($value);++$A)
    {
      $aux_parte_numerica =ord(substr($value,$A,1)) % 16;
      $aux_parte_zona = (ord(substr($value,$A,1)) - $aux_parte_numerica) / 16;
      $parte_numerica = $tab_val[$aux_parte_numerica];
      $parte_zona = $tab_val[$aux_parte_zona];
      $hex .=  $parte_zona.$parte_numerica;
    }
    return $hex;
  }

  private static function getLength(&$len,&$bytes,$data)
  {
    $len = ord($data[1]);
    $bytes = 0;
    # Testa se tamanho menor/igual a 127 bytes..
    # Neste caso $len  ja he o tamanho do conteudo
    if ($len & 0x80)
    {
      # Testa se tamanho indefinido (nao deve ocorrer em uma codifica��o DER.
      if($len == chr(0x80))
      {
        # Tamanho indefinido, limitado por 0x0000h
        $len = strpos($data,chr(0x00).chr(0x00));
        $bytes = 0;
      }
      else
      {
        # he tamanho definido. diz quantos bytes formam o tamanho....
        $bytes = $len & 0x0f;
        $len = 0;
        for ($i = 0; $i < $bytes; ++$i)
        {
          $len = ($len << 8) | ord($data[$i + 2]);
        }
      }
    }
  }

  private static function xBase128($ab,$q,$flag )
  {
    $abc = $ab;
    if( $q > 127 )
    {
      $abc = self::xBase128($abc, floor($q / 128), 0 );
    }
    $q = $q % 128;
    if( $flag)
    {
      $abc[] = $q;
    }
    else
    {
      $abc[] = 0x80 | $q;
    }
    return $abc;
  }

  private static function OIDtoHex($oid)
  {
    $abBinary = array();
    $partes = explode('.',$oid);
    $n = 0;
    $b = 0;
    $partes_count = count($partes);
    for($n = 0; $n < $partes_count; ++$n)
    {
      if($n==0)
      {
        $b = 40 * $partes[$n];
      }
      elseif($n==1)
      {
        $b +=  $partes[$n];
        $abBinary[] = $b;
      }
      else
      {
        $abBinary = self::xBase128($abBinary, $partes[$n], 1 );
      }
    }
    $value =chr(0x06) . chr(count($abBinary));
    foreach($abBinary as $item)
    {
      $value .= chr($item);
    }
    return $value;
  }

  private static function CrlParseASN($data,$context_especific = false)
  {
    // Tabela de OIDs .
    $_oids = array(
        '2.5.4.3' => 'CN',
        '2.5.4.4' => 'Surname',
        '2.5.4.6' => 'C',
        '2.5.4.7' => 'Cidade',
        '2.5.4.8' => 'Estatdo',
        '2.5.4.9' => 'StreetAddress',
        '2.5.4.10' => 'O',
        '2.5.4.11' => 'OU',
        '2.5.4.12' => 'Title',
        '2.5.4.20' => 'TelephoneNumber',
        '2.5.4.42' => 'GivenName',
        '2.5.29.14' => 'id-ce-subjectKeyIdentifier',
        '2.5.29.15' => 'id-ce-keyUsage',
        '2.5.29.17' => 'id-ce-subjectAltName',
        '2.5.29.19' => 'id-ce-basicConstraints',
        '2.5.29.20' => 'id-ce-cRLNumber',
        '2.5.29.31' => 'id-ce-CRLDistributionPoints',
        '2.5.29.32' => 'id-ce-certificatePolicies',
        '2.5.29.35' => 'id-ce-authorityKeyIdentifier',
        '2.5.29.37' => 'id-ce-extKeyUsage',
        '1.2.840.113549.1.9.1' => 'Email',
        '1.2.840.113549.1.1.1' => 'RSAEncryption',
        '1.2.840.113549.1.1.2' => 'md2WithRSAEncryption',
        '1.2.840.113549.1.1.4' => 'md5withRSAEncryption',
        '1.2.840.113549.1.1.5' => 'SHA-1WithRSAEncryption',
        '1.2.840.10040.4.3' => 'id-dsa-with-sha-1',
        '1.3.6.1.5.5.7.3.2' => 'id_kp_clientAuth',
        '1.3.6.1.5.5.7.3.4' => 'id_kp_securityemail',
        '1.3.6.1.5.5.7.2.1' => 'id_certificatePolicies',
        '2.16.840.1.113730.1.1' => 'netscape-cert-type',
        '2.16.840.1.113730.1.2' => 'netscape-base-url',
        '2.16.840.1.113730.1.3' => 'netscape-revocation-url',
        '2.16.840.1.113730.1.4' => 'netscape-ca-revocation-url',
        '2.16.840.1.113730.1.7' => 'netscape-cert-renewal-url',
        '2.16.840.1.113730.1.8' => 'netscape-ca-policy-url',
        '2.16.840.1.113730.1.12' => 'netscape-ssl-server-name',
        '2.16.840.1.113730.1.13' => 'netscape-comment',
        '2.16.76.1.2.1' => 'A1',
        '2.16.76.1.2.3' => 'A3',
        '2.16.76.1.2.1.16' => 'Certification Practice Statement pointer',
        '2.16.76.1.3.1' => 'Dados do cert parte 1',
        '2.16.76.1.3.5' => 'Dados do cert parte 2',
        '2.16.76.1.3.6' => 'Dados do cert parte 3',
        '0.9.2342.19200300.100.1.25' => ' domainComponent',
        '1.2.36.68980861.1.1.10' => ' Signet pilot',
        '1.2.36.68980861.1.1.11' => ' Signet intraNet',
        '1.2.36.68980861.1.1.2' => ' Signet personal',
        '1.2.36.68980861.1.1.20' => ' Signet securityPolicy',
        '1.2.36.68980861.1.1.3' => ' Signet business',
        '1.2.36.68980861.1.1.4' => ' Signet legal',
        '1.2.36.75878867.1.100.1.1' => ' Certificates Australia policyIdentifier',
        '1.2.752.34.1' => ' seis-cp',
        '1.2.752.34.1.1' => ' SEIS certificatePolicy-s10',
        '1.2.752.34.2' => ' SEIS pe',
        '1.2.752.34.3' => ' SEIS at',
        '1.2.752.34.3.1' => ' SEIS at-personalIdentifier',
        '1.2.840.10040.2.1' => ' holdinstruction-none',
        '1.2.840.10040.2.2' => ' holdinstruction-callissuer',
        '1.2.840.10040.2.3' => ' holdinstruction-reject',
        '1.2.840.10040.4.1' => ' dsa',
        '1.2.840.10040.4.3' => ' dsaWithSha1',
        '1.2.840.10045.1' => ' fieldType',
        '1.2.840.10045.1.1' => ' prime-field',
        '1.2.840.10045.1.2' => ' characteristic-two-field',
        '1.2.840.10045.1.2.1' => ' ecPublicKey',
        '1.2.840.10045.1.2.3' => ' characteristic-two-basis',
        '1.2.840.10045.1.2.3.1' => ' onBasis',
        '1.2.840.10045.1.2.3.2' => ' tpBasis',
        '1.2.840.10045.1.2.3.3' => ' ppBasis',
        '1.2.840.10045.2' => ' publicKeyType',
        '1.2.840.10045.2.1' => ' ecPublicKey',
        '1.2.840.10046.2.1' => ' dhPublicNumber',
        '1.2.840.113533.7' => ' nsn',
        '1.2.840.113533.7.65' => ' nsn-ce',
        '1.2.840.113533.7.65.0' => ' entrustVersInfo',
        '1.2.840.113533.7.66' => ' nsn-alg',
        '1.2.840.113533.7.66.10' => ' cast5CBC',
        '1.2.840.113533.7.66.11' => ' cast5MAC',
        '1.2.840.113533.7.66.12' => ' pbeWithMD5AndCAST5-CBC',
        '1.2.840.113533.7.66.13' => ' passwordBasedMac',
        '1.2.840.113533.7.66.3' => ' cast3CBC',
        '1.2.840.113533.7.67' => ' nsn-oc',
        '1.2.840.113533.7.67.0' => ' entrustUser',
        '1.2.840.113533.7.68' => ' nsn-at',
        '1.2.840.113533.7.68.0' => ' entrustCAInfo',
        '1.2.840.113533.7.68.10' => ' attributeCertificate',
        '1.2.840.113549.1.1' => ' pkcs-1',
        '1.2.840.113549.1.1.1' => ' rsaEncryption',
        '1.2.840.113549.1.1.2' => ' md2withRSAEncryption',
        '1.2.840.113549.1.1.3' => ' md4withRSAEncryption',
        '1.2.840.113549.1.1.4' => ' md5withRSAEncryption',
        '1.2.840.113549.1.1.5' => ' sha1withRSAEncryption',
        '1.2.840.113549.1.1.6' => ' rsaOAEPEncryptionSET',
        '1.2.840.113549.1.9.16.2.11' => 'SMIMEEncryptionKeyPreference',
        '1.2.840.113549.1.12' => ' pkcs-12',
        '1.2.840.113549.1.12.1' => ' pkcs-12-PbeIds',
        '1.2.840.113549.1.12.1.1' => ' pbeWithSHAAnd128BitRC4',
        '1.2.840.113549.1.12.1.2' => ' pbeWithSHAAnd40BitRC4',
        '1.2.840.113549.1.12.1.3' => ' pbeWithSHAAnd3-KeyTripleDES-CBC',
        '1.2.840.113549.1.12.1.4' => ' pbeWithSHAAnd2-KeyTripleDES-CBC',
        '1.2.840.113549.1.12.1.5' => ' pbeWithSHAAnd128BitRC2-CBC',
        '1.2.840.113549.1.12.1.6' => ' pbeWithSHAAnd40BitRC2-CBC',
        '1.2.840.113549.1.12.10' => ' pkcs-12Version1',
        '1.2.840.113549.1.12.10.1' => ' pkcs-12BadIds',
        '1.2.840.113549.1.12.10.1.1' => ' pkcs-12-keyBag',
        '1.2.840.113549.1.12.10.1.2' => ' pkcs-12-pkcs-8ShroudedKeyBag',
        '1.2.840.113549.1.12.10.1.3' => ' pkcs-12-certBag',
        '1.2.840.113549.1.12.10.1.4' => ' pkcs-12-crlBag',
        '1.2.840.113549.1.12.10.1.5' => ' pkcs-12-secretBag',
        '1.2.840.113549.1.12.10.1.6' => ' pkcs-12-safeContentsBag',
        '1.2.840.113549.1.12.2' => ' pkcs-12-ESPVKID',
        '1.2.840.113549.1.12.2.1' => ' pkcs-12-PKCS8KeyShrouding',
        '1.2.840.113549.1.12.3' => ' pkcs-12-BagIds',
        '1.2.840.113549.1.12.3.1' => ' pkcs-12-keyBagId',
        '1.2.840.113549.1.12.3.2' => ' pkcs-12-certAndCRLBagId',
        '1.2.840.113549.1.12.3.3' => ' pkcs-12-secretBagId',
        '1.2.840.113549.1.12.3.4' => ' pkcs-12-safeContentsId',
        '1.2.840.113549.1.12.3.5' => ' pkcs-12-pkcs-8ShroudedKeyBagId',
        '1.2.840.113549.1.12.4' => ' pkcs-12-CertBagID',
        '1.2.840.113549.1.12.4.1' => ' pkcs-12-X509CertCRLBagID',
        '1.2.840.113549.1.12.4.2' => ' pkcs-12-SDSICertBagID',
        '1.2.840.113549.1.12.5' => ' pkcs-12-OID',
        '1.2.840.113549.1.12.5.1' => ' pkcs-12-PBEID',
        '1.2.840.113549.1.12.5.1.1' => ' pkcs-12-PBEWithSha1And128BitRC4',
        '1.2.840.113549.1.12.5.1.2' => ' pkcs-12-PBEWithSha1And40BitRC4',
        '1.2.840.113549.1.12.5.1.3' => ' pkcs-12-PBEWithSha1AndTripleDESCBC',
        '1.2.840.113549.1.12.5.1.4' => ' pkcs-12-PBEWithSha1And128BitRC2CBC',
        '1.2.840.113549.1.12.5.1.5' => ' pkcs-12-PBEWithSha1And40BitRC2CBC',
        '1.2.840.113549.1.12.5.1.6' => ' pkcs-12-PBEWithSha1AndRC4',
        '1.2.840.113549.1.12.5.1.7' => ' pkcs-12-PBEWithSha1AndRC2CBC',
        '1.2.840.113549.1.12.5.2' => ' pkcs-12-EnvelopingID',
        '1.2.840.113549.1.12.5.2.1' => ' pkcs-12-RSAEncryptionWith128BitRC4',
        '1.2.840.113549.1.12.5.2.2' => ' pkcs-12-RSAEncryptionWith40BitRC4',
        '1.2.840.113549.1.12.5.2.3' => ' pkcs-12-RSAEncryptionWithTripleDES',
        '1.2.840.113549.1.12.5.3' => ' pkcs-12-SignatureID',
        '1.2.840.113549.1.12.5.3.1' => ' pkcs-12-RSASignatureWithSHA1Digest',
        '1.2.840.113549.1.3' => ' pkcs-3',
        '1.2.840.113549.1.3.1' => ' dhKeyAgreement',
        '1.2.840.113549.1.5' => ' pkcs-5',
        '1.2.840.113549.1.5.1' => ' pbeWithMD2AndDES-CBC',
        '1.2.840.113549.1.5.10' => ' pbeWithSHAAndDES-CBC',
        '1.2.840.113549.1.5.3' => ' pbeWithMD5AndDES-CBC',
        '1.2.840.113549.1.5.4' => ' pbeWithMD2AndRC2-CBC',
        '1.2.840.113549.1.5.6' => ' pbeWithMD5AndRC2-CBC',
        '1.2.840.113549.1.5.9' => ' pbeWithMD5AndXOR',
        '1.2.840.113549.1.7' => ' pkcs-7',
        '1.2.840.113549.1.7.1' => ' data',
        '1.2.840.113549.1.7.2' => ' signedData',
        '1.2.840.113549.1.7.3' => ' envelopedData',
        '1.2.840.113549.1.7.4' => ' signedAndEnvelopedData',
        '1.2.840.113549.1.7.5' => ' digestData',
        '1.2.840.113549.1.7.6' => ' encryptedData',
        '1.2.840.113549.1.7.7' => ' dataWithAttributes',
        '1.2.840.113549.1.7.8' => ' encryptedPrivateKeyInfo',
        '1.2.840.113549.1.9' => ' pkcs-9',
        '1.2.840.113549.1.9.1' => ' emailAddress',
        '1.2.840.113549.1.9.10' => ' issuerAndSerialNumber',
        '1.2.840.113549.1.9.11' => ' passwordCheck',
        '1.2.840.113549.1.9.12' => ' publicKey',
        '1.2.840.113549.1.9.13' => ' signingDescription',
        '1.2.840.113549.1.9.14' => ' extensionReq',
        '1.2.840.113549.1.9.15' => ' sMIMECapabilities',
        '1.2.840.113549.1.9.15.1' => ' preferSignedData',
        '1.2.840.113549.1.9.15.2' => ' canNotDecryptAny',
        '1.2.840.113549.1.9.15.3' => ' receiptRequest',
        '1.2.840.113549.1.9.15.4' => ' receipt',
        '1.2.840.113549.1.9.15.5' => ' contentHints',
        '1.2.840.113549.1.9.15.6' => ' mlExpansionHistory',
        '1.2.840.113549.1.9.16' => ' id-sMIME',
        '1.2.840.113549.1.9.16.0' => ' id-mod',
        '1.2.840.113549.1.9.16.0.1' => ' id-mod-cms',
        '1.2.840.113549.1.9.16.0.2' => ' id-mod-ess',
        '1.2.840.113549.1.9.16.1' => ' id-ct',
        '1.2.840.113549.1.9.16.1.1' => ' id-ct-receipt',
        '1.2.840.113549.1.9.16.2' => ' id-aa',
        '1.2.840.113549.1.9.16.2.1' => ' id-aa-receiptRequest',
        '1.2.840.113549.1.9.16.2.2' => ' id-aa-securityLabel',
        '1.2.840.113549.1.9.16.2.3' => ' id-aa-mlExpandHistory',
        '1.2.840.113549.1.9.16.2.4' => ' id-aa-contentHint',
        '1.2.840.113549.1.9.2' => ' unstructuredName',
        '1.2.840.113549.1.9.20' => ' friendlyName',
        '1.2.840.113549.1.9.21' => ' localKeyID',
        '1.2.840.113549.1.9.22' => ' certTypes',
        '1.2.840.113549.1.9.22.1' => ' x509Certificate',
        '1.2.840.113549.1.9.22.2' => ' sdsiCertificate',
        '1.2.840.113549.1.9.23' => ' crlTypes',
        '1.2.840.113549.1.9.23.1' => ' x509Crl',
        '1.2.840.113549.1.9.3' => ' contentType',
        '1.2.840.113549.1.9.4' => ' messageDigest',
        '1.2.840.113549.1.9.5' => ' signingTime',
        '1.2.840.113549.1.9.6' => ' countersignature',
        '1.2.840.113549.1.9.7' => ' challengePassword',
        '1.2.840.113549.1.9.8' => ' unstructuredAddress',
        '1.2.840.113549.1.9.9' => ' extendedCertificateAttributes',
        '1.2.840.113549.2' => ' digestAlgorithm',
        '1.2.840.113549.2.2' => ' md2',
        '1.2.840.113549.2.4' => ' md4',
        '1.2.840.113549.2.5' => ' md5',
        '1.2.840.113549.3' => ' encryptionAlgorithm',
        '1.2.840.113549.3.10' => ' desCDMF',
        '1.2.840.113549.3.2' => ' rc2CBC',
        '1.2.840.113549.3.3' => ' rc2ECB',
        '1.2.840.113549.3.4' => ' rc4',
        '1.2.840.113549.3.5' => ' rc4WithMAC',
        '1.2.840.113549.3.6' => ' DESX-CBC',
        '1.2.840.113549.3.7' => ' DES-EDE3-CBC',
        '1.2.840.113549.3.8' => ' RC5CBC',
        '1.2.840.113549.3.9' => ' RC5-CBCPad',
        '1.2.840.113556.4.3' => ' microsoftExcel',
        '1.2.840.113556.4.4' => ' titledWithOID',
        '1.2.840.113556.4.5' => ' microsoftPowerPoint',
        '1.3.133.16.840.9.84' => ' x9-84',
        '1.3.133.16.840.9.84.0' => ' x9-84-Module',
        '1.3.133.16.840.9.84.0.1' => ' x9-84-Biometrics',
        '1.3.133.16.840.9.84.0.2' => ' x9-84-CMS',
        '1.3.133.16.840.9.84.0.3' => ' x9-84-Identifiers',
        '1.3.133.16.840.9.84.1' => ' biometric',
        '1.3.133.16.840.9.84.1.0' => ' id-unknown-Type',
        '1.3.133.16.840.9.84.1.1' => ' id-body-Odor',
        '1.3.133.16.840.9.84.1.10' => ' id-palm',
        '1.3.133.16.840.9.84.1.11' => ' id-retina',
        '1.3.133.16.840.9.84.1.12' => ' id-signature',
        '1.3.133.16.840.9.84.1.13' => ' id-speech-Pattern',
        '1.3.133.16.840.9.84.1.14' => ' id-thermal-Image',
        '1.3.133.16.840.9.84.1.15' => ' id-vein-Pattern',
        '1.3.133.16.840.9.84.1.16' => ' id-thermal-Face-Image',
        '1.3.133.16.840.9.84.1.17' => ' id-thermal-Hand-Image',
        '1.3.133.16.840.9.84.1.18' => ' id-lip-Movement',
        '1.3.133.16.840.9.84.1.19' => ' id-gait',
        '1.3.133.16.840.9.84.1.2' => ' id-dna',
        '1.3.133.16.840.9.84.1.3' => ' id-ear-Shape',
        '1.3.133.16.840.9.84.1.4' => ' id-facial-Features',
        '1.3.133.16.840.9.84.1.5' => ' id-finger-Image',
        '1.3.133.16.840.9.84.1.6' => ' id-finger-Geometry',
        '1.3.133.16.840.9.84.1.7' => ' id-hand-Geometry',
        '1.3.133.16.840.9.84.1.8' => ' id-iris-Features',
        '1.3.133.16.840.9.84.1.9' => ' id-keystroke-Dynamics',
        '1.3.133.16.840.9.84.2' => ' processing-algorithm',
        '1.3.133.16.840.9.84.3' => ' matching-method',
        '1.3.133.16.840.9.84.4' => ' format-Owner',
        '1.3.133.16.840.9.84.4.0' => ' cbeff-Owner',
        '1.3.133.16.840.9.84.4.1' => ' ibia-Owner',
        '1.3.133.16.840.9.84.4.1.1' => ' id-ibia-SAFLINK',
        '1.3.133.16.840.9.84.4.1.10' => ' id-ibia-SecuGen',
        '1.3.133.16.840.9.84.4.1.11' => ' id-ibia-PreciseBiometric',
        '1.3.133.16.840.9.84.4.1.12' => ' id-ibia-Identix',
        '1.3.133.16.840.9.84.4.1.13' => ' id-ibia-DERMALOG',
        '1.3.133.16.840.9.84.4.1.14' => ' id-ibia-LOGICO',
        '1.3.133.16.840.9.84.4.1.15' => ' id-ibia-NIST',
        '1.3.133.16.840.9.84.4.1.16' => ' id-ibia-A3Vision',
        '1.3.133.16.840.9.84.4.1.17' => ' id-ibia-NEC',
        '1.3.133.16.840.9.84.4.1.18' => ' id-ibia-STMicroelectronics',
        '1.3.133.16.840.9.84.4.1.2' => ' id-ibia-Bioscrypt',
        '1.3.133.16.840.9.84.4.1.3' => ' id-ibia-Visionics',
        '1.3.133.16.840.9.84.4.1.4' => ' id-ibia-InfineonTechnologiesAG',
        '1.3.133.16.840.9.84.4.1.5' => ' id-ibia-IridianTechnologies',
        '1.3.133.16.840.9.84.4.1.6' => ' id-ibia-Veridicom',
        '1.3.133.16.840.9.84.4.1.7' => ' id-ibia-CyberSIGN',
        '1.3.133.16.840.9.84.4.1.8' => ' id-ibia-eCryp.',
        '1.3.133.16.840.9.84.4.1.9' => ' id-ibia-FingerprintCardsAB',
        '1.3.133.16.840.9.84.4.2' => ' x9-Owner',
        '1.3.14.2.26.5' => ' sha',
        '1.3.14.3.2.1.1' => ' rsa',
        '1.3.14.3.2.10' => ' desMAC',
        '1.3.14.3.2.11' => ' rsaSignature',
        '1.3.14.3.2.12' => ' dsa',
        '1.3.14.3.2.13' => ' dsaWithSHA',
        '1.3.14.3.2.14' => ' mdc2WithRSASignature',
        '1.3.14.3.2.15' => ' shaWithRSASignature',
        '1.3.14.3.2.16' => ' dhWithCommonModulus',
        '1.3.14.3.2.17' => ' desEDE',
        '1.3.14.3.2.18' => ' sha',
        '1.3.14.3.2.19' => ' mdc-2',
        '1.3.14.3.2.2' => ' md4WitRSA',
        '1.3.14.3.2.2.1' => ' sqmod-N',
        '1.3.14.3.2.20' => ' dsaCommon',
        '1.3.14.3.2.21' => ' dsaCommonWithSHA',
        '1.3.14.3.2.22' => ' rsaKeyTransport',
        '1.3.14.3.2.23' => ' keyed-hash-seal',
        '1.3.14.3.2.24' => ' md2WithRSASignature',
        '1.3.14.3.2.25' => ' md5WithRSASignature',
        '1.3.14.3.2.26' => ' sha1',
        '1.3.14.3.2.27' => ' dsaWithSHA1',
        '1.3.14.3.2.28' => ' dsaWithCommonSHA1',
        '1.3.14.3.2.29' => ' sha-1WithRSAEncryption',
        '1.3.14.3.2.3' => ' md5WithRSA',
        '1.3.14.3.2.3.1' => ' sqmod-NwithRSA',
        '1.3.14.3.2.4' => ' md4WithRSAEncryption',
        '1.3.14.3.2.6' => ' desECB',
        '1.3.14.3.2.7' => ' desCBC',
        '1.3.14.3.2.8' => ' desOFB',
        '1.3.14.3.2.9' => ' desCFB',
        '1.3.14.3.3.1' => ' simple-strong-auth-mechanism',
        '1.3.14.7.2.1.1' => ' ElGamal',
        '1.3.14.7.2.3.1' => ' md2WithRSA',
        '1.3.14.7.2.3.2' => ' md2WithElGamal',
        '1.3.36.3' => ' algorithm',
        '1.3.36.3.1' => ' encryptionAlgorithm',
        '1.3.36.3.1.1' => ' des',
        '1.3.36.3.1.1.1.1' => ' desECBPad',
        '1.3.36.3.1.1.1.1.1' => ' desECBPadISO',
        '1.3.36.3.1.1.2.1' => ' desCBCPad',
        '1.3.36.3.1.1.2.1.1' => ' desCBCPadISO',
        '1.3.36.3.1.2' => ' idea',
        '1.3.36.3.1.2.1' => ' ideaECB',
        '1.3.36.3.1.2.1.1' => ' ideaECBPad',
        '1.3.36.3.1.2.1.1.1' => ' ideaECBPadISO',
        '1.3.36.3.1.2.2' => ' ideaCBC',
        '1.3.36.3.1.2.2.1' => ' ideaCBCPad',
        '1.3.36.3.1.2.2.1.1' => ' ideaCBCPadISO',
        '1.3.36.3.1.2.3' => ' ideaOFB',
        '1.3.36.3.1.2.4' => ' ideaCFB',
        '1.3.36.3.1.3' => ' des-3',
        '1.3.36.3.1.3.1.1' => ' des-3ECBPad',
        '1.3.36.3.1.3.1.1.1' => ' des-3ECBPadISO',
        '1.3.36.3.1.3.2.1' => ' des-3CBCPad',
        '1.3.36.3.1.3.2.1.1' => ' des-3CBCPadISO',
        '1.3.36.3.2' => ' hashAlgorithm',
        '1.3.36.3.2.1' => ' ripemd160',
        '1.3.36.3.2.2' => ' ripemd128',
        '1.3.36.3.2.3' => ' ripemd256',
        '1.3.36.3.2.4' => ' mdc2singleLength',
        '1.3.36.3.2.5' => ' mdc2doubleLength',
        '1.3.36.3.3' => ' signatureAlgorithm',
        '1.3.36.3.3.1' => ' rsa',
        '1.3.36.3.3.1.1' => ' rsaMitSHA-1',
        '1.3.36.3.3.1.2' => ' rsaMitRIPEMD160',
        '1.3.36.3.3.2' => ' ellipticCurve',
        '1.3.36.3.4' => ' signatureScheme',
        '1.3.36.3.4.1' => ' iso9796-1',
        '1.3.36.3.4.2.1' => ' iso9796-2',
        '1.3.36.3.4.2.2' => ' iso9796-2rsa',
        '1.3.36.4' => ' attribute',
        '1.3.36.5' => ' policy',
        '1.3.36.6' => ' api',
        '1.3.36.6.1' => ' manufacturerSpecific',
        '1.3.36.6.2' => ' functionalitySpecific',
        '1.3.36.7' => ' api',
        '1.3.36.7.1' => ' keyAgreement',
        '1.3.36.7.2' => ' keyTransport',
        '1.3.6.1.4.1.2428.10.1.1' => ' UNINETT policyIdentifier',
        '1.3.6.1.4.1.2712.10' => ' ICE-TEL policyIdentifier',
        '1.3.6.1.4.1.3029.32.1' => ' cryptlibEnvelope',
        '1.3.6.1.4.1.3029.32.2' => ' cryptlibPrivateKey',
        '1.3.6.1.4.1.311' => ' Microsoft OID',
        '1.3.6.1.4.1.311.10' => ' Crypto 2.0',
        '1.3.6.1.4.1.311.10.1' => ' certTrustList',
        '1.3.6.1.4.1.311.10.1.1' => ' szOID_SORTED_CTL',
        '1.3.6.1.4.1.311.10.10' => ' Microsoft CMC OIDs',
        '1.3.6.1.4.1.311.10.10.1' => ' szOID_CMC_ADD_ATTRIBUTES',
        '1.3.6.1.4.1.311.10.11' => ' Microsoft certificate property OIDs',
        '1.3.6.1.4.1.311.10.11.1' => ' szOID_CERT_PROP_ID_PREFIX',
        '1.3.6.1.4.1.311.10.12' => ' CryptUI',
        '1.3.6.1.4.1.311.10.12.1' => ' szOID_ANY_APPLICATION_POLICY',
        '1.3.6.1.4.1.311.10.2' => ' nextUpdateLocation',
        '1.3.6.1.4.1.311.10.3.1' => ' certTrustListSigning',
        '1.3.6.1.4.1.311.10.3.10' => ' szOID_KP_QUALIFIED_SUBORDINATION',
        '1.3.6.1.4.1.311.10.3.11' => ' szOID_KP_KEY_RECOVERY',
        '1.3.6.1.4.1.311.10.3.12' => ' szOID_KP_DOCUMENT_SIGNING',
        '1.3.6.1.4.1.311.10.3.2' => ' timeStampSigning',
        '1.3.6.1.4.1.311.10.3.3' => ' serverGatedCrypto',
        '1.3.6.1.4.1.311.10.3.3.1' => ' szOID_SERIALIZED',
        '1.3.6.1.4.1.311.10.3.4' => ' encryptedFileSystem',
        '1.3.6.1.4.1.311.10.3.4.1' => ' szOID_EFS_RECOVERY',
        '1.3.6.1.4.1.311.10.3.5' => ' szOID_WHQL_CRYPTO',
        '1.3.6.1.4.1.311.10.3.6' => ' szOID_NT5_CRYPTO',
        '1.3.6.1.4.1.311.10.3.7' => ' szOID_OEM_WHQL_CRYPTO',
        '1.3.6.1.4.1.311.10.3.8' => ' szOID_EMBEDDED_NT_CRYPTO',
        '1.3.6.1.4.1.311.10.3.9' => ' szOID_ROOT_LIST_SIGNER',
        '1.3.6.1.4.1.311.10.4.1' => ' yesnoTrustAttr',
        '1.3.6.1.4.1.311.10.5.1' => ' szOID_DRM',
        '1.3.6.1.4.1.311.10.5.2' => ' szOID_DRM_INDIVIDUALIZATION',
        '1.3.6.1.4.1.311.10.6.1' => ' szOID_LICENSES',
        '1.3.6.1.4.1.311.10.6.2' => ' szOID_LICENSE_SERVER',
        '1.3.6.1.4.1.311.10.7' => ' szOID_MICROSOFT_RDN_PREFIX',
        '1.3.6.1.4.1.311.10.7.1' => ' szOID_KEYID_RDN',
        '1.3.6.1.4.1.311.10.8.1' => ' szOID_REMOVE_CERTIFICATE',
        '1.3.6.1.4.1.311.10.9.1' => ' szOID_CROSS_CERT_DIST_POINTS',
        '1.3.6.1.4.1.311.12' => ' Catalog',
        '1.3.6.1.4.1.311.12.1.1' => ' szOID_CATALOG_LIST',
        '1.3.6.1.4.1.311.12.1.2' => ' szOID_CATALOG_LIST_MEMBER',
        '1.3.6.1.4.1.311.12.2.1' => ' CAT_NAMEVALUE_OBJID',
        '1.3.6.1.4.1.311.12.2.2' => ' CAT_MEMBERINFO_OBJID',
        '1.3.6.1.4.1.311.13' => ' Microsoft PKCS10 OIDs',
        '1.3.6.1.4.1.311.13.1' => ' szOID_RENEWAL_CERTIFICATE',
        '1.3.6.1.4.1.311.13.2.1' => ' szOID_ENROLLMENT_NAME_VALUE_PAIR',
        '1.3.6.1.4.1.311.13.2.2' => ' szOID_ENROLLMENT_CSP_PROVIDER',
        '1.3.6.1.4.1.311.13.2.3' => ' OS Version',
        '1.3.6.1.4.1.311.15' => ' Microsoft Java',
        '1.3.6.1.4.1.311.16' => ' Microsoft Outlook/Exchange',
        '1.3.6.1.4.1.311.16.4' => ' Outlook Express',
        '1.3.6.1.4.1.311.17' => ' Microsoft PKCS12 attributes',
        '1.3.6.1.4.1.311.17.1' => ' szOID_LOCAL_MACHINE_KEYSET',
        '1.3.6.1.4.1.311.18' => ' Microsoft Hydra',
        '1.3.6.1.4.1.311.19' => ' Microsoft ISPU Test',
        '1.3.6.1.4.1.311.2' => ' Authenticode',
        '1.3.6.1.4.1.311.2.1.10' => ' spcAgencyInfo',
        '1.3.6.1.4.1.311.2.1.11' => ' spcStatementType',
        '1.3.6.1.4.1.311.2.1.12' => ' spcSpOpusInfo',
        '1.3.6.1.4.1.311.2.1.14' => ' certExtensions',
        '1.3.6.1.4.1.311.2.1.15' => ' spcPelmageData',
        '1.3.6.1.4.1.311.2.1.18' => ' SPC_RAW_FILE_DATA_OBJID',
        '1.3.6.1.4.1.311.2.1.19' => ' SPC_STRUCTURED_STORAGE_DATA_OBJID',
        '1.3.6.1.4.1.311.2.1.20' => ' spcLink',
        '1.3.6.1.4.1.311.2.1.21' => ' individualCodeSigning',
        '1.3.6.1.4.1.311.2.1.22' => ' commercialCodeSigning',
        '1.3.6.1.4.1.311.2.1.25' => ' spcLink',
        '1.3.6.1.4.1.311.2.1.26' => ' spcMinimalCriteriaInfo',
        '1.3.6.1.4.1.311.2.1.27' => ' spcFinancialCriteriaInfo',
        '1.3.6.1.4.1.311.2.1.28' => ' spcLink',
        '1.3.6.1.4.1.311.2.1.29' => ' SPC_HASH_INFO_OBJID',
        '1.3.6.1.4.1.311.2.1.30' => ' SPC_SIPINFO_OBJID',
        '1.3.6.1.4.1.311.2.1.4' => ' spcIndirectDataContext',
        '1.3.6.1.4.1.311.2.2' => ' CTL for Software Publishers Trusted CAs',
        '1.3.6.1.4.1.311.2.2.1' => ' szOID_TRUSTED_CODESIGNING_CA_LIST',
        '1.3.6.1.4.1.311.2.2.2' => ' szOID_TRUSTED_CLIENT_AUTH_CA_LIST',
        '1.3.6.1.4.1.311.2.2.3' => ' szOID_TRUSTED_SERVER_AUTH_CA_LIST',
        '1.3.6.1.4.1.311.20' => ' Microsoft Enrollment Infrastructure',
        '1.3.6.1.4.1.311.20.1' => ' szOID_AUTO_ENROLL_CTL_USAGE',
        '1.3.6.1.4.1.311.20.2' => ' szOID_ENROLL_CERTTYPE_EXTENSION',
        '1.3.6.1.4.1.311.20.2.1' => ' szOID_ENROLLMENT_AGENT',
        '1.3.6.1.4.1.311.20.2.2' => ' szOID_KP_SMARTCARD_LOGON',
        '1.3.6.1.4.1.311.20.2.3' => ' szOID_NT_PRINCIPAL_NAME',
        '1.3.6.1.4.1.311.20.3' => ' szOID_CERT_MANIFOLD',
        '1.3.6.1.4.1.311.21' => ' Microsoft CertSrv Infrastructure',
        '1.3.6.1.4.1.311.21.1' => ' szOID_CERTSRV_CA_VERSION',
        '1.3.6.1.4.1.311.21.20' => ' Client Information',
        '1.3.6.1.4.1.311.25' => ' Microsoft Directory Service',
        '1.3.6.1.4.1.311.25.1' => ' szOID_NTDS_REPLICATION',
        '1.3.6.1.4.1.311.3' => ' Time Stamping',
        '1.3.6.1.4.1.311.3.2.1' => ' SPC_TIME_STAMP_REQUEST_OBJID',
        '1.3.6.1.4.1.311.30' => ' IIS',
        '1.3.6.1.4.1.311.31' => ' Windows updates and service packs',
        '1.3.6.1.4.1.311.31.1' => ' szOID_PRODUCT_UPDATE',
        '1.3.6.1.4.1.311.4' => ' Permissions',
        '1.3.6.1.4.1.311.40' => ' Fonts',
        '1.3.6.1.4.1.311.41' => ' Microsoft Licensing and Registration',
        '1.3.6.1.4.1.311.42' => ' Microsoft Corporate PKI (ITG)',
        '1.3.6.1.4.1.311.88' => ' CAPICOM',
        '1.3.6.1.4.1.311.88.1' => ' szOID_CAPICOM_VERSION',
        '1.3.6.1.4.1.311.88.2' => ' szOID_CAPICOM_ATTRIBUTE',
        '1.3.6.1.4.1.311.88.2.1' => ' szOID_CAPICOM_DOCUMENT_NAME',
        '1.3.6.1.4.1.311.88.2.2' => ' szOID_CAPICOM_DOCUMENT_DESCRIPTION',
        '1.3.6.1.4.1.311.88.3' => ' szOID_CAPICOM_ENCRYPTED_DATA',
        '1.3.6.1.4.1.311.88.3.1' => ' szOID_CAPICOM_ENCRYPTED_CONTENT',
        '1.3.6.1.5.5.7' => ' pkix',
        '1.3.6.1.5.5.7.1' => ' privateExtension',
        '1.3.6.1.5.5.7.1.1' => ' authorityInfoAccess',
        '1.3.6.1.5.5.7.12.2' => ' CMC Data',
        '1.3.6.1.5.5.7.2' => ' policyQualifierIds',
        '1.3.6.1.5.5.7.2.1' => ' cps',
        '1.3.6.1.5.5.7.2.2' => ' unotice',
        '1.3.6.1.5.5.7.3' => ' keyPurpose',
        '1.3.6.1.5.5.7.3.1' => ' serverAuth',
        '1.3.6.1.5.5.7.3.2' => ' clientAuth',
        '1.3.6.1.5.5.7.3.3' => ' codeSigning',
        '1.3.6.1.5.5.7.3.4' => ' emailProtection',
        '1.3.6.1.5.5.7.3.5' => ' ipsecEndSystem',
        '1.3.6.1.5.5.7.3.6' => ' ipsecTunnel',
        '1.3.6.1.5.5.7.3.7' => ' ipsecUser',
        '1.3.6.1.5.5.7.3.8' => ' timeStamping',
        '1.3.6.1.5.5.7.4' => ' cmpInformationTypes',
        '1.3.6.1.5.5.7.4.1' => ' caProtEncCert',
        '1.3.6.1.5.5.7.4.2' => ' signKeyPairTypes',
        '1.3.6.1.5.5.7.4.3' => ' encKeyPairTypes',
        '1.3.6.1.5.5.7.4.4' => ' preferredSymmAlg',
        '1.3.6.1.5.5.7.4.5' => ' caKeyUpdateInfo',
        '1.3.6.1.5.5.7.4.6' => ' currentCRL',
        '1.3.6.1.5.5.7.48.1' => ' ocsp',
        '1.3.6.1.5.5.7.48.2' => ' caIssuers',
        '1.3.6.1.5.5.8.1.1' => ' HMAC-MD5',
        '1.3.6.1.5.5.8.1.2' => ' HMAC-SHA',
        '2.16.840.1.101.2.1.1.1' => ' sdnsSignatureAlgorithm',
        '2.16.840.1.101.2.1.1.10' => ' mosaicKeyManagementAlgorithm',
        '2.16.840.1.101.2.1.1.11' => ' sdnsKMandSigAlgorithm',
        '2.16.840.1.101.2.1.1.12' => ' mosaicKMandSigAlgorithm',
        '2.16.840.1.101.2.1.1.13' => ' SuiteASignatureAlgorithm',
        '2.16.840.1.101.2.1.1.14' => ' SuiteAConfidentialityAlgorithm',
        '2.16.840.1.101.2.1.1.15' => ' SuiteAIntegrityAlgorithm',
        '2.16.840.1.101.2.1.1.16' => ' SuiteATokenProtectionAlgorithm',
        '2.16.840.1.101.2.1.1.17' => ' SuiteAKeyManagementAlgorithm',
        '2.16.840.1.101.2.1.1.18' => ' SuiteAKMandSigAlgorithm',
        '2.16.840.1.101.2.1.1.19' => ' mosaicUpdatedSigAlgorithm',
        '2.16.840.1.101.2.1.1.2' => ' mosaicSignatureAlgorithm',
        '2.16.840.1.101.2.1.1.20' => ' mosaicKMandUpdSigAlgorithms',
        '2.16.840.1.101.2.1.1.21' => ' mosaicUpdatedIntegAlgorithm',
        '2.16.840.1.101.2.1.1.22' => ' mosaicKeyEncryptionAlgorithm',
        '2.16.840.1.101.2.1.1.3' => ' sdnsConfidentialityAlgorithm',
        '2.16.840.1.101.2.1.1.4' => ' mosaicConfidentialityAlgorithm',
        '2.16.840.1.101.2.1.1.5' => ' sdnsIntegrityAlgorithm',
        '2.16.840.1.101.2.1.1.6' => ' mosaicIntegrityAlgorithm',
        '2.16.840.1.101.2.1.1.7' => ' sdnsTokenProtectionAlgorithm',
        '2.16.840.1.101.2.1.1.8' => ' mosaicTokenProtectionAlgorithm',
        '2.16.840.1.101.2.1.1.9' => ' sdnsKeyManagementAlgorithm',
        '2.16.840.1.113730.1' => ' cert-extension',
        '2.16.840.1.113730.1.1' => ' netscape-cert-type',
        '2.16.840.1.113730.1.10' => ' EntityLogo',
        '2.16.840.1.113730.1.11' => ' UserPicture',
        '2.16.840.1.113730.1.12' => ' netscape-ssl-server-name',
        '2.16.840.1.113730.1.13' => ' netscape-comment',
        '2.16.840.1.113730.1.2' => ' netscape-base-url',
        '2.16.840.1.113730.1.3' => ' netscape-revocation-url',
        '2.16.840.1.113730.1.4' => ' netscape-ca-revocation-url',
        '2.16.840.1.113730.1.7' => ' netscape-cert-renewal-url',
        '2.16.840.1.113730.1.8' => ' netscape-ca-policy-url',
        '2.16.840.1.113730.1.9' => ' HomePage-url',
        '2.16.840.1.113730.2' => ' data-type',
        '2.16.840.1.113730.2.1' => ' GIF',
        '2.16.840.1.113730.2.2' => ' JPEG',
        '2.16.840.1.113730.2.3' => ' URL',
        '2.16.840.1.113730.2.4' => ' HTML',
        '2.16.840.1.113730.2.5' => ' netscape-cert-sequence',
        '2.16.840.1.113730.2.6' => ' netscape-cert-url',
        '2.16.840.1.113730.3' => ' directory',
        '2.16.840.1.113730.4.1' => ' serverGatedCrypto',
        '2.16.840.1.113733.1.6.3' => ' Unknown Verisign extension',
        '2.16.840.1.113733.1.6.6' => ' Unknown Verisign extension',
        '2.16.840.1.113733.1.7.1.1' => ' Verisign certificatePolicy',
        '2.16.840.1.113733.1.7.1.1.1' => ' Unknown Verisign policy qualifier',
        '2.16.840.1.113733.1.7.1.1.2' => ' Unknown Verisign policy qualifier',
        '2.23.133' => ' TCPA',
        '2.23.133.1' => ' tcpa_specVersion',
        '2.23.133.2' => ' tcpa_attribute',
        '2.23.133.2.1' => ' tcpa_at_tpmManufacturer',
        '2.23.133.2.10' => ' tcpa_at_securityQualities',
        '2.23.133.2.11' => ' tcpa_at_tpmProtectionProfile',
        '2.23.133.2.12' => ' tcpa_at_tpmSecurityTarget',
        '2.23.133.2.13' => ' tcpa_at_foundationProtectionProfile',
        '2.23.133.2.14' => ' tcpa_at_foundationSecurityTarget',
        '2.23.133.2.15' => ' tcpa_at_tpmIdLabel',
        '2.23.133.2.2' => ' tcpa_at_tpmModel',
        '2.23.133.2.3' => ' tcpa_at_tpmVersion',
        '2.23.133.2.4' => ' tcpa_at_platformManufacturer',
        '2.23.133.2.5' => ' tcpa_at_platformModel',
        '2.23.133.2.6' => ' tcpa_at_platformVersion',
        '2.23.133.2.7' => ' tcpa_at_componentManufacturer',
        '2.23.133.2.8' => ' tcpa_at_componentModel',
        '2.23.133.2.9' => ' tcpa_at_componentVersion',
        '2.23.133.3' => ' tcpa_protocol',
        '2.23.133.3.1' => ' tcpa_prtt_tpmIdProtocol',
        '2.23.42.0' => ' contentType',
        '2.23.42.0.0' => ' PANData',
        '2.23.42.0.1' => ' PANToken',
        '2.23.42.0.2' => ' PANOnly',
        '2.23.42.1' => ' msgExt',
        '2.23.42.10' => ' national',
        '2.23.42.10.192' => ' Japan',
        '2.23.42.2' => ' field',
        '2.23.42.2.0' => ' fullName',
        '2.23.42.2.1' => ' givenName',
        '2.23.42.2.10' => ' amount',
        '2.23.42.2.2' => ' familyName',
        '2.23.42.2.3' => ' birthFamilyName',
        '2.23.42.2.4' => ' placeName',
        '2.23.42.2.5' => ' identificationNumber',
        '2.23.42.2.6' => ' month',
        '2.23.42.2.7' => ' date',
        '2.23.42.2.7.11' => ' accountNumber',
        '2.23.42.2.7.12' => ' passPhrase',
        '2.23.42.2.8' => ' address',
        '2.23.42.2.9' => ' telephone',
        '2.23.42.3' => ' attribute',
        '2.23.42.3.0' => ' cert',
        '2.23.42.3.0.0' => ' rootKeyThumb',
        '2.23.42.3.0.1' => ' additionalPolicy',
        '2.23.42.4' => ' algorithm',
        '2.23.42.5' => ' policy',
        '2.23.42.5.0' => ' root',
        '2.23.42.6' => ' module',
        '2.23.42.7' => ' certExt',
        '2.23.42.7.0' => ' hashedRootKey',
        '2.23.42.7.1' => ' certificateType',
        '2.23.42.7.2' => ' merchantData',
        '2.23.42.7.3' => ' cardCertRequired',
        '2.23.42.7.4' => ' tunneling',
        '2.23.42.7.5' => ' setExtensions',
        '2.23.42.7.6' => ' setQualifier',
        '2.23.42.8' => ' brand',
        '2.23.42.8.1' => ' IATA-ATA',
        '2.23.42.8.30' => ' Diners',
        '2.23.42.8.34' => ' AmericanExpress',
        '2.23.42.8.4' => ' VISA',
        '2.23.42.8.5' => ' MasterCard',
        '2.23.42.8.6011' => ' Novus',
        '2.23.42.9' => ' vendor',
        '2.23.42.9.0' => ' GlobeSet',
        '2.23.42.9.1' => ' IBM',
        '2.23.42.9.10' => ' Griffin',
        '2.23.42.9.11' => ' Certicom',
        '2.23.42.9.12' => ' OSS',
        '2.23.42.9.13' => ' TenthMountain',
        '2.23.42.9.14' => ' Antares',
        '2.23.42.9.15' => ' ECC',
        '2.23.42.9.16' => ' Maithean',
        '2.23.42.9.17' => ' Netscape',
        '2.23.42.9.18' => ' Verisign',
        '2.23.42.9.19' => ' BlueMoney',
        '2.23.42.9.2' => ' CyberCash',
        '2.23.42.9.20' => ' Lacerte',
        '2.23.42.9.21' => ' Fujitsu',
        '2.23.42.9.22' => ' eLab',
        '2.23.42.9.23' => ' Entrust',
        '2.23.42.9.24' => ' VIAnet',
        '2.23.42.9.25' => ' III',
        '2.23.42.9.26' => ' OpenMarket',
        '2.23.42.9.27' => ' Lexem',
        '2.23.42.9.28' => ' Intertrader',
        '2.23.42.9.29' => ' Persimmon',
        '2.23.42.9.3' => ' Terisa',
        '2.23.42.9.30' => ' NABLE',
        '2.23.42.9.31' => ' espace-net',
        '2.23.42.9.32' => ' Hitachi',
        '2.23.42.9.33' => ' Microsoft',
        '2.23.42.9.34' => ' NEC',
        '2.23.42.9.35' => ' Mitsubishi',
        '2.23.42.9.36' => ' NCR',
        '2.23.42.9.37' => ' e-COMM',
        '2.23.42.9.38' => ' Gemplus',
        '2.23.42.9.4' => ' RSADSI',
        '2.23.42.9.5' => ' VeriFone',
        '2.23.42.9.6' => ' TrinTech',
        '2.23.42.9.7' => ' BankGate',
        '2.23.42.9.8' => ' GTE',
        '2.23.42.9.9' => ' CompuSource',
        '2.5.29.1' => ' authorityKeyIdentifier',
        '2.5.29.10' => ' basicConstraints',
        '2.5.29.11' => ' nameConstraints',
        '2.5.29.12' => ' policyConstraints',
        '2.5.29.13' => ' basicConstraints',
        '2.5.29.14' => ' subjectKeyIdentifier',
        '2.5.29.15' => ' keyUsage',
        '2.5.29.16' => ' privateKeyUsagePeriod',
        '2.5.29.17' => ' subjectAltName',
        '2.5.29.18' => ' issuerAltName',
        '2.5.29.19' => ' basicConstraints',
        '2.5.29.2' => ' keyAttributes',
        '2.5.29.20' => ' cRLNumber',
        '2.5.29.21' => ' cRLReason',
        '2.5.29.22' => ' expirationDate',
        '2.5.29.23' => ' instructionCode',
        '2.5.29.24' => ' invalidityDate',
        '2.5.29.26' => ' issuingDistributionPoint',
        '2.5.29.27' => ' deltaCRLIndicator',
        '2.5.29.28' => ' issuingDistributionPoint',
        '2.5.29.29' => ' certificateIssuer',
        '2.5.29.3' => ' certificatePolicies',
        '2.5.29.30' => ' nameConstraints',
        '2.5.29.31' => ' cRLDistributionPoints',
        '2.5.29.32' => ' certificatePolicies',
        '2.5.29.33' => ' policyMappings',
        '2.5.29.34' => ' policyConstraints',
        '2.5.29.35' => ' authorityKeyIdentifier',
        '2.5.29.36' => ' policyConstraints',
        '2.5.29.37' => ' extKeyUsage',
        '2.5.29.4' => ' keyUsageRestriction',
        '2.5.29.5' => ' policyMapping',
        '2.5.29.6' => ' subtreesConstraint',
        '2.5.29.7' => ' subjectAltName',
        '2.5.29.8' => ' issuerAltName',
        '2.5.29.9' => ' subjectDirectoryAttributes',
        '2.5.4.0' => ' objectClass',
        '2.5.4.1' => ' aliasObjectName',
        '2.5.4.12' => ' title',
        '2.5.4.13' => ' description',
        '2.5.4.14' => ' searchGuide',
        '2.5.4.15' => ' businessCategory',
        '2.5.4.16' => ' postalAddress',
        '2.5.4.17' => ' postalCode',
        '2.5.4.18' => ' postOfficeBox',
        '2.5.4.19' => ' physicalDeliveryOfficeName',
        '2.5.4.2' => ' knowledgeInformation',
        '2.5.4.20' => ' telephoneNumber',
        '2.5.4.21' => ' telexNumber',
        '2.5.4.22' => ' teletexTerminalIdentifier',
        '2.5.4.23' => ' facsimileTelephoneNumber',
        '2.5.4.24' => ' x121Address',
        '2.5.4.25' => ' internationalISDNNumber',
        '2.5.4.26' => ' registeredAddress',
        '2.5.4.27' => ' destinationIndicator',
        '2.5.4.28' => ' preferredDeliveryMehtod',
        '2.5.4.29' => ' presentationAddress',
        '2.5.4.30' => ' supportedApplicationContext',
        '2.5.4.31' => ' member',
        '2.5.4.32' => ' owner',
        '2.5.4.33' => ' roleOccupant',
        '2.5.4.34' => ' seeAlso',
        '2.5.4.35' => ' userPassword',
        '2.5.4.36' => ' userCertificate',
        '2.5.4.37' => ' caCertificate',
        '2.5.4.38' => ' authorityRevocationList',
        '2.5.4.39' => ' certificateRevocationList',
        '2.5.4.40' => ' crossCertificatePair',
        '2.5.4.41' => ' givenName',
        '2.5.4.42' => ' givenName',
        '2.5.4.5' => ' serialNumber',
        '2.5.4.52' => ' supportedAlgorithms',
        '2.5.4.53' => ' deltaRevocationList',
        '2.5.4.58' => ' crossCertificatePair',
        '2.5.4.9' => ' streetAddress',
        '2.5.8' => ' X.500-Algorithms',
        '2.5.8.1' => ' X.500-Alg-Encryption',
        '2.5.8.1.1' => ' rsa',
        '2.16.76.1.1' => 'DPC',
        '2.16.76.1.1.0' => 'DPC da AC Raiz',
        '2.16.76.1.1.1' => 'DPC da AC Presid�ncia',
        '2.16.76.1.1.2' => 'DPC da AC Serpro',
        '2.16.76.1.1.3' => 'DPC da SERASA Autoridade Certificadora Principal - ACP',
        '2.16.76.1.1.4' => 'DPC da SERASA Autoridade Certificadora - AC',
        '2.16.76.1.1.5' => 'DPC da AC CertiSign na ICP�Brasil',
        '2.16.76.1.1.6' => 'DPC da AC CertiSign SPB na ICP�Brasil',
        '2.16.76.1.1.7' => 'DPC da SERASA Certificadora Digital',
        '2.16.76.1.1.8' => 'DPC da AC SRF',
        '2.16.76.1.1.9' => 'DPC da AC CAIXA',
        '2.16.76.1.1.10' => 'DPC da AC CAIXA IN',
        '2.16.76.1.1.11' => 'DPC da AC CAIXA PJ',
        '2.16.76.1.1.12' => 'DPC da AC CAIXA PF',
        '2.16.76.1.1.13' => 'DPC da AC SERPRO SRF',
        '2.16.76.1.1.14' => 'DPC da Autoridade Certificadora CertiSign M�ltipla',
        '2.16.76.1.1.15' => 'DPC da Autoridade Certificadora CertiSign para Secretaria da Receita Federal',
        '2.16.76.1.1.16' => 'DPC da AC SERASA SRF',
        '2.16.76.1.1.17' => 'DPC da Autoridade Certificadora Imprensa Oficial � SP',
        '2.16.76.1.1.18' => 'DPC da Autoridade Certificadora PRODEMGE',
        '2.16.76.1.1.19' => 'DPC da Autoridade Certificadora do Sistema Justi�a Federal - AC�JUS',
        '2.16.76.1.1.20' => 'Declara��o   de   Pr�ticas   de   Certifica��o   da   Autoridade   Certificadora   do SERPRO Final - DPC SERPRO ACF',
        '2.16.76.1.1' => 'DPC',
        '2.16.76.1.1.21' => 'Declara��o de Pr�ticas de Certifica��o da Autoridade Certificadora SINCOR',
        '2.16.76.1.1.22' => 'Declara��o de Pr�ticas de Certifica��o da Autoridade Certificadora Imprensa Oficial SP SRF',
        '2.16.76.1.1.23' => 'Declara��o de Pr�ticas de Certifica��o da AC FENACOR',
        '2.16.76.1.1.24' => 'Declara��o de Pr�ticas de Certifica��o da Autoridade Certificadora SERPRO� JUS',
        '2.16.76.1.1.25' => 'DPC da AC Caixa Justi�a',
        '2.16.76.1.1.26' => 'DPC da Autoridade Certificadora Imprensa Oficial SP (AC IMESP)',
        '2.16.76.1.1.27' => 'DPC da Autoridade Certificadora PRODEMGE SRF',
        '2.16.76.1.1.28' => 'Declara��o de Pr�ticas de Certifica��o da Autoridade Certificadora CertSign  para a Justi�a',
        '2.16.76.1.1.29' => 'DPC da AC SERASA JUS',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.1' => 'A1',
        '2.16.76.1.2.1.1' => 'Pol�tica de Certificados da ACSERPRO para certificados SERPRO�SPB � PC SERPRO�SPB',
        '2.16.76.1.2.1.2' => 'Pol�tica de Certificados para certificados da SERASA Autoridade Certificadora',
        '2.16.76.1.2.1.3' => 'Pol�tica   de   Certificados   da   Autoridade   Certificadora   da   Presid�ncia   da Rep�blica - PCA1',
        '2.16.76.1.2.1.4' => 'Pol�tica   de   Certificado   da   Autoridade   Certificadora   CertiSign   Certificadora Digital para o Sistema de Pagamentos Brasileiro na ICP�Brasil� PC da AC CertiSign SPB na ICP�Brasil',
        '2.16.76.1.2.1.5' => 'Pol�tica de Certificados SEPROA1',
        '2.16.76.1.2.1.6' => 'Pol�tica de Certificado Digital para Certificado de Assinatura Digital Tipo A1 -  SERASA CD',
        '2.16.76.1.2.1.7' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A1 da AC Caixa IN',
        '2.16.76.1.2.1.8' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A1 da AC Caixa PF',
        '2.16.76.1.2.1.9' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A1 da AC Caixa PJ',
        '2.16.76.1.2.1.10' => 'Pol�tica de Certificados  da  Autoridade  Certificadora  do  Serpro�SRF  para certificados de assinatura digital do tipo A1 (PCSerpro�SRFA1)',
        '2.16.76.1.2.1.11' => 'Pol�tica de  Certificado de  Assinatura Digital do Tipo  A1 da  Autoridade  Certificadora  CertiSign  M�ltipla  na  Infra�estrutura  de  Chaves  P�blicas  Brasileira',
        '2.16.76.1.2.1.12' => 'Pol�tica  de  Certificado  de   Assinatura   Digital   Tipo   A1   da   Autoridade  Certificadora CertiSign para a Secretaria da Receita Federal',
        '2.16.76.1.2.1.13' => 'Pol�tica de Certificado de Assinatura Digital Tipo A1 da AC SERASA SRF',
        '2.16.76.1.2.1.14' => 'Pol�tica  de Certificado  de  Assinatura   Digital   Tipo  A1  da  Autoridade Certificadora Imprensa Oficial � SP',
        '2.16.76.1.2.1.15' => 'Pol�tica  de  Certificado  de  Assinatura   Digital  Tipo  A1  da  Autoridade  Certificadora PRODEMGE',
        '2.16.76.1.2.1.16' => 'Pol�tica de Certificados SERPRO do Tipo A1 - PC SERPRO ACF A1',
        '2.16.76.1.2.1.17' => 'Pol�tica de Certificados do SERPRO - SPB - PC SERPRO ACF SPB',
        '2.16.76.1.2.1.18' => 'Pol�tica  de  Certificado  de  Assinatura  Digital  Tipo  A1  da  Autoridade Certificadora SINCOR',
        '2.16.76.1.2.1.19' => 'Pol�tica de Certificado  de  Assinatura  Digital  Tipo  A1  da  Autoridade Certificadora SINCOR para Corretores de Seguros',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.1' => 'A1',
        '2.16.76.1.2.1.20' => 'Pol�tica  de  Certificado  de  Assinatura  Digital  Tipo  A1  da  Autoridade Certificadora Imprensa Oficial SP SRF',
        '2.16.76.1.2.1.21' => 'Pol�tica de Certificados SERPRO�JUS do tipo A1 � PCSERPROJUSA1',
        '2.16.76.1.2.1.22' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A1 da AC Caixa Justi�a',
        '2.16.76.1.2.1.23' => 'Pol�tica  de Certificado de  Assinatura  Tipo  A1 da  Autoridade  Certificadora PRODEMGE SRF',
        '2.16.76.1.2.1.24' => 'Pol�tica de Certificado de Assinatura Digital Tipo A1 da Autoridade Certificadora CertiSign para a Justi�a',
        '2.16.76.1.2.1.25' => 'Pol�tica de Certificado Digital da AC  SERASA�JUS para Certificados Tipo A1',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.2' => 'A2',
        '2.16.76.1.2.2.1' => 'Pol�tica de Certificado Digital para Certificado de Assinatura Digital  Tipo A2 - SERASA CD',
        '2.16.76.1.2.2.2' => 'Pol�tica de Certificado Digital para Certificado de Assinatura Digital Tipo A2 da AC SERASA SRF',
        '2.16.76.1.2.2.3' => 'Pol�tica  de  Certificado  de  Assinatura  Digital   do  Tipo  A2  da  Autoridade  Certificadora  CertiSign  �ltipla  na  Infra�estrutura  de Chaves P�blicas Brasileira',
        '2.16.76.1.2.2.4' => 'Pol�tica  de  Certificado  de  Assinatura  Digital  do Tipo  A2  da Autoridade Certificadora Imprensa Oficial � SP',
        '2.16.76.1.2.2.5' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A2 da AC Caixa Justi�a',
        '2.16.76.1.2.2.6' => 'Pol�tica de Certificado de Assinatura Digital Tipo A2 da Autoridade Certificadora CertiSign para a Justi�a',
        '2.16.76.1.2.2.7' => 'Pol�tica de Certificado  Digital da  AC SERASA�JUS para Certificados Tipo A2',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.3' => 'A3',
        '2.16.76.1.2.3.1' => 'Pol�tica de Certificados da Autoridade Certificadora da Presid�ncia da Rep�blica - PC ACPR',
        '2.16.76.1.2.3.2' => 'Pol�tica  de  Certificados   da  Autoridade  Certificadora do  SERPRO para certificados SERPRO do tipo A3 - PCSERPROA3',
        '2.16.76.1.2.3.3' => 'Pol�tica de Certificado Digital para Certificado de Assinatura Digital Tipo A3 - SERASA CD',
        '2.16.76.1.2.3.4' => 'Pol�tica de Certificados da Autoridade Certificadora do Serpro�SRF para certificados de assinatura digital do tipo A3 (PCSerpro�SRFA3)',
        '2.16.76.1.2.3.5' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A3 da  Autoridade Certificadora CertiSign M�ltipla na Infra�estrutura de Chaves P�blicas Brasileira',
        '2.16.76.1.2.3.6' => 'Pol�tica de Certificado de Assinatura Digital Tipo A3 da Autoridade Certificadora CertiSign para a Secretaria da Receita Federal na Infra�estrutura de Chaves P�blicas Brasileira',
        '2.16.76.1.2.3.7' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A3 da AC Caixa  IN',
        '2.16.76.1.2.3.8' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A3 da AC Caixa  PF',
        '2.16.76.1.2.3.9' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A3 da AC Caixa   PJ',
        '2.16.76.1.2.3.10' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A3 da AC SERASA SRF',
        '2.16.76.1.2.3.11' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A3 da  Autoridade Certificadora  Imprensa Oficial � SP',
        '2.16.76.1.2.3.12' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A3 da  Autoridade Certificadora PRODEMGE',
        '2.16.76.1.2.3.13' => 'Pol�tica de Certificados SERPRO do Tipo A3 - PC SERPRO A3',
        '2.16.76.1.2.3.14' => 'Pol�tica de Certificado de Assinatura Digital Tipo A3 da Autoridade Certificadora SINCOR',
        '2.16.76.1.2.3.15' => 'Pol�tica de Certificado de Assinatura Digital Tipo A3 da Autoridade  Certificadora SINCOR para Corretores de Seguros',
        '2.16.76.1.2.3.16' => 'Pol�tica de Certificado de Assinatura Digital Tipo A3 da Autoridade  Certificadora Imprensa Oficial SP SRF',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.3' => 'A3',
        '2.16.76.1.2.3.17' => 'Pol�tica de Certificado da AC FENACOR A3',
        '2.16.76.1.2.3.18' => 'Pol�tica  de  Certificados  SERPRO�JUS  do  tipo  A3 PCSERPROJUSA3',
        '2.16.76.1.2.3.19' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A3 da AC Caixa Justi�a',
        '2.16.76.1.2.3.20' => 'Pol�tica  de  Certificado  de  Assinatura   Tipo   A3   da   Autoridade Certificadora PRODEMGE SRF',
        '2.16.76.1.2.3.21' => 'Pol�tica de Certificado de Assinatura Digital Tipo A3 da Autoridade  Certificadora CertiSign para a Justi�a',
        '2.16.76.1.2.3.22' => 'Pol�tica   de   Certificado   Digital   da   AC     SERASA�JUS   para Certificados Tipo A3',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.4' => 'A4',
        '2.16.76.1.2.4.1' => 'Pol�tica de Certificado Digital para Certificado de Assinatura Digital  Tipo A4 - SERASA CD;',
        '2.16.76.1.2.4.2' => 'VAGO',
        '2.16.76.1.2.4.3' => 'Pol�tica de Certificado de Assinatura Digital do Tipo A4 da  Autoridade Certificadora CertiSign M�ltipla na Infra�estrutura de Chaves P�blicas Brasileira',
        '2.16.76.1.2.4.4' => 'Pol�tica de Certificado de Assinatura Digital Tipo A4 da Autoridade Certificadora CertiSign para a Secretaria da Receita Federal na Infra�estrutura de Chaves P�blicas Brasileira',
        '2.16.76.1.2.4.5' => 'Pol�tica de Certificado de Assinatura Digital Tipo A4 da Autoridade Certificadora Imprensa Oficial � SP',
        '2.16.76.1.2.4.6' => 'Pol�tica de Certificado de Assinatura Digital Tipo A4 da Autoridade Certificadora Imprensa Oficial - SP SRF',
        '2.16.76.1.2.4.7' => 'Pol�tica   de   Certificado   de   Assinatura   Tipo   A4   da   Autoridade Certificadora PRODEMGE SRF',
        '2.16.76.1.2.4.8' => 'Pol�tica de Certificado de Assinatura Digital Tipo A4 da Autoridade Certificadora CertiSign para a Justi�a',
        '2.16.76.1.2.4.9' => 'VAGO',
        '2.16.76.1.2.4.10' => 'Pol�tica de Certificado Digital  a  AC SERASA�JUS  para Certificados Tipo A4',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.101' => 'S1',
        '2.16.76.1.2.101.1' => 'Pol�tica de Certificado Digital para Certificado de Sigilo Tipo S1 -  SERASA CD',
        '2.16.76.1.2.101.2' => 'Pol�tica de Certificado de Sigilo Tipo S1 da Autoridade Certificadora Imprensa Oficial � SP',
        '2.16.76.1.2.101.3' => 'Pol�tica  de  Certificado  de  Sigilo  do  Tipo  S1  da   Autoridade Certificadora CertiSign M�ltipla',
        '2.16.76.1.2.101.4' => 'Pol�tica   de   Certificado   de   Sigilo   do   Tipo   S1   da   Autoridade Certificadora PRODEMGE',
        '2.16.76.1.2.101.5' => 'Pol�tica de Certificado de Assinatura Digital do Tipo S1 da AC Caixa Justi�a',
        '2.16.76.1.2.101.6' => 'Pol�tica de Certificado de Assinatura Digital Tipo S1 da Autoridade Certificadora CertiSign para a Justi�a',
        '2.16.76.1.2.101.7' => 'Pol�tica   de   Certificado   Digital   da   AC     SERASA�JUS   para Cerficados Tipo S1',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.102' => 'S2',
        '2.16.76.1.2.102.1' => 'Pol�tica de Certificado Digital para Certificado de Sigilo Tipo S2 - SERASA CD',
        '2.16.76.1.2.102.2' => 'Pol�tica de Certificado de Sigilo Tipo S2 da Autoridade Certificadora Imprensa Oficial � SP',
        '2.16.76.1.2.102.3' => 'Pol�tica   de   Certificado   de   Sigilo   do   Tipo   S2   da   Autoridade Certificadora CertiSign M�ltipla',
        '2.16.76.1.2.102.4' => 'Pol�tica de Certificado de Assinatura Digital do Tipo S2 da AC Caixa Justi�a',
        '2.16.76.1.2.102.5' => 'Pol�tica de Certificado de Assinatura Digital Tipo S2 da Autoridade Certificadora CertiSign para a Justi�a',
        '2.16.76.1.2.102.6' => 'Pol�tica  de  Certificado  Digital  da   AC  SERASA�JUS   para Certificados Tipo S2',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.103' => 'S3',
        '2.16.76.1.2.103.1' => 'Pol�tica de Certificado Digital para Certificado de Sigilo Tipo S3 -  SERASA CD',
        '2.16.76.1.2.103.2' => 'VAGO',
        '2.16.76.1.2.103.3' => 'Pol�tica   de   Certificado   de   Sigilo   do   Tipo   S3   da   Autoridade Certificadora CertiSign M�ltipla',
        '2.16.76.1.2.103.4' => 'Pol�tica de Certificado de Sigilo Tipo S3 da Autoridade Certificadora Imprensa Oficial � SP',
        '2.16.76.1.2.103.5' => 'Pol�tica de Certificado de Sigilo Tipo S3 da Autoridade Certificadora PRODEMGE',
        '2.16.76.1.2.103.6' => 'Pol�tica de Certificado de Assinatura Digital do Tipo S3 da AC Caixa Justi�a',
        '2.16.76.1.2.103.7' => 'Pol�tica de Certificado de Assinatura Digital Tipo S3 da Autoridade Certificadora CertiSign para a Justi�a',
        '2.16.76.1.2.103.8' => 'Pol�tica   de   Certificado   Digital   da   AC     SERASA�JUS   para Certificados Tipo S3',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.104' => 'S4',
        '2.16.76.1.2.104.1' => 'Pol�tica de Certificado Digital para Certificado de Sigilo Tipo S4 - SERASA CD',
        '2.16.76.1.2.104.2' => 'VAGO',
        '2.16.76.1.2.104.3' => 'Pol�tica   de   Certificado   de   Sigilo   do   Tipo   S4   da   Autoridade Certificadora CertiSign M�ltipla',
        '2.16.76.1.2.104.4' => 'Pol�tica de Certificado de Sigilo Tipo S4 da Autoridade Certificadora Imprensa Oficial � SP',
        '2.16.76.1.2.104.5' => 'Pol�tica de Certificado de Assinatura Digital Tipo S4 da Autoridade Certificadora CertiSign para a Justi�a',
        '2.16.76.1.2.104.6' => 'Pol�tica   de   Certificado   Digital   da   AC     SERASA�JUS   para Certificados Tipo S4',
        '2.16.76.1.2' => 'PC',
        '2.16.76.1.2.201' => 'PC de AC',
        '2.16.76.1.2.201.1' => 'PC da Serasa Autoridade Certificadora Principal - ACP',
        '2.16.76.1.2.201.2' => 'PC da AC CertiSign na ICP�Brasil',
        '2.16.76.1.2.201.3' => 'PC da AC SRF',
        '2.16.76.1.2.201.4' => 'Pol�tica de Certificados da Autoridade Certificadora Caixa',
        '2.16.76.1.2.201.5' => 'PC da Autoridade Certificadora do Sistema Justi�a Federal - AC� JUS',
        '2.16.76.1.2.201.6' => 'PC da Autoridade Certificadora do SERPRO (AC SERPRO)',
        '2.16.76.1.2.201.7' => 'PC da Autoridade Certificadora Imprensa Oficial SP (AC IMESP)',
        '2.16.76.1.3' => 'Atributos Obrigat�rios de Certificados',
        '2.16.76.1.3.1' => 'campo otherName em certificado de pessoa f�sica',
        '2.16.76.1.3.2' => 'campo otherName em certificado de pessoa  jur�dica',
        '2.16.76.1.3.3' => 'campo otherName em certificado de pessoa  jur�dica',
        '2.16.76.1.3.4' => 'campo otherName em certificado de pessoa jur�dica',
        '2.16.76.1.3.5' => 'campo otherName em certificado de pessoa f�sica',
        '2.16.76.1.3.6' => 'campo otherName em certificado de pessoa f�sica',
        '2.16.76.1.3.7' => 'campo otherName em certificado de pessoa jur�dica',
        '2.16.76.1.4' => 'Atributos Opcionais de  Certificados',
        '2.16.76.1.4.1' => 'Entidades Sindicais',
        '2.16.76.1.4.1.1' => 'SINCOR',
        '2.16.76.1.4.1.1.1' => 'N�mero de registro do corretor associado');


    $result = array();

    while (strlen($data) > 1)
    {
      $class = ord($data[0]);
      switch ($class)
      {
        case 0x30:
          // Sequence
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $sequence_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $values = self::CrlParseASN($sequence_data);
          if (!is_array($values) || is_string($values[0]))
          {
            $values = array($values);
          }
          $result[] = array('sequence (' . $len . ')' , $values);
          break;

        case 0x31:
          // Set of
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $sequence_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('set (' . $len . ')' , self::CrlParseASN($sequence_data));
          break;

        case 0x01:
          // Boolean type
          $boolean_value = (ord($data[2]) == 0xff);
          $data = substr($data, 3);
          $result[] = array('boolean (1)' , $boolean_value);
          break;

        case 0x02:
          // Integer type
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $integer_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('integer(' . $len . ')', self::imprimirHex($integer_data));
          break;
        /*
         if($len == 16)
         {
         $result[] = array('integer(' . $len . ')', $integer_data);
         break;
         }
         else
         {
         $value = 0;
         if ($len <= 4)
         {
         // Method works fine for small integers
         for ($i = 0; $i < strlen($integer_data); ++$i)
         {
         $value = ($value << 8) | ord($integer_data[$i]);
         }
         }
         else
         {
         // Method works for arbitrary length integers
         if (extension_loaded('bcmath'))
         {
         for ($i = 0; $i < strlen($integer_data); ++$i)
         {
         $value = bcadd(bcmul($value, 256), ord($integer_data[$i]));
         }
         }
         else
         {
         $value = -1;
         }
         }
         $result[] = array('integer(' . $len . ')', $value);
         break;
         }
         */
        case 0x03:
          // Bitstring type
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $bitstring_data = substr($data, 2+$bytes ,  $len);
          $data = substr($data, 2 + $bytes + $len);
          //$result[] = array('bit string (' . $len . ')' ,self::CrlParseASN($bitstring_data));
          $result[] = array('bit string (' . $len . ')' ,'UnsedBits:'.ord($bitstring_data[0]).':'.ord($bitstring_data[1]));
          break;

        case 0x04:
          // Octetstring type
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $octectstring_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          if($context_especific)
          {
            $result[] = array('octet string(' . $len . ')'  , $octectstring_data);
          }
          else
          {
            $aux = array('octet string (' . $len . ')' , self::CrlParseASN($octectstring_data));
            if(is_array($aux[1])) {
              $aux_r = '';
            } else {
              $aux_r = @substr($aux[1],0,7);
            }
            if($aux_r == 'UNKNOWN')
            {
              $aux = array('octet string (' . $len . ')' , self::imprimirHex($octectstring_data));
            }
            $result[]=$aux;
          }

          break;

        case 0x0C:
          // UTF8 STRING
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $octectstring_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          if($context_especific)
          {
            $result[] = array('utf8 string(' . $len . ')'  , $octectstring_data);
          }
          else
          {
            $result[] = array('utf8 string (' . $len . ')' , self::CrlParseASN($octectstring_data));
          }
          break;

        case 0x05:
          // Null type
          $data = substr($data, 2);
          $result[] = array('null', null);
          break;

        case 0x06:
          // Object identifier type
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $oid_data = substr($data, 2 + $bytes, $len);
          $x_len = $data[1];
          $data = substr($data, 2 + $bytes + $len);

          // Unpack the OID
          $plain  = floor(ord($oid_data[0]) / 40);
          $plain .= '.' . ord($oid_data[0]) % 40;

          $value = 0;
          $i = 1;
          while ($i < strlen($oid_data))
          {
            $value = $value << 7;
            $value = $value | (ord($oid_data[$i]) & 0x7f);

            if (!(ord($oid_data[$i]) & 0x80))
            {
              $plain .= '.' . $value;
              $value = 0;
            }
            ++$i;
          }

          if (isset($_oids[$plain]))
          {
            $result[] =  array('oid(' . $len . '): '  . $plain, $_oids[$plain]);
          }
          else
          {
            $result[] = array('oid(' . $len . '): '  . $plain, $plain);
          }
          break;

        case 0x16:
          // Character string type
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $string_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('IA5 String (' . $len . ')'  , $string_data);
          break;

        case 0x12:
        case 0x14:
        case 0x15:
        case 0x81:
          // Character string type
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $string_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('string (' . $len . ')'  , $string_data);
          break;

        case 0x80:
          // Character string type
          $len = strlen($data)-2;
          $bytes = 0;
          //self::getLength(&$len,&$bytes,$data);
          $data_aux = $data;
          $string_data = substr($data, strlen($data)-20);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('string (' . $len . ')'  , self::imprimirHex($string_data));
          break;

        case 0x13:
        case 0x86:
          // Printable string type
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $string_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('Printable String (' . $len . ')'  , $string_data);
          break;

        case 0x17:
          // Time types
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $time_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('utctime (' . $len . ')'  , $time_data);
          break;

        case 0x82:
          // X509v3 extensions?
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $sequence_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('extension : X509v3 extensions (' . $len . ')'  , array(self::CrlParseASN($sequence_data)));
          break;

        case 0xa0:
        case 0xa4:
          // Extensions
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $extension_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('Context Especific (' . $len . ')' , array(self::CrlParseASN($extension_data,true)));
          break;

        case 0xa3:
          // Extensions
          $len = ord($data[1]);
          $bytes = 0;
          self::getLength($len,$bytes,$data);
          $extension_data = substr($data, 2 + $bytes, $len);
          $data = substr($data, 2 + $bytes + $len);
          $result[] = array('extension (0xA3)  (' . $len . ')' ,array(self::CrlParseASN($extension_data)));
          break;

        case 0xe6:
          $extension_data = substr($data, 0, 1);
          $data = substr($data, 1);
          $result[] = array('extension (0xE6) (' . $len . ')'  , dechex($extension_data));
          break;

        case 0xa1:
          $extension_data = substr($data, 0, 1);
          $data = substr($data, 6);
          $result[] = array('extension (0xA1) (' . $len . ')'  , dechex($extension_data));
          break;

        default:
          // Unknown
          $result[] = '' .  $data;
          $data = '';
          break;
      }
    }

    return (count($result) > 1) ? $result : array_pop($result);

  }

#============================================================================================
# Transforma o certificado do formato PEM para o formato DER ...
  private static function pem2der($pem_data)
  {
    $begin = "CERTIFICATE-----";
    $end   = "-----END";
    $pem_data = substr($pem_data, strpos($pem_data, $begin)+strlen($begin));
    $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
    $der = base64_decode($pem_data);
    return $der;
  }

  private static function testarP7m($msg)
  {
    // oids pesquisadas:
    //                                1.2.840.113549.1.7.2     assinatura digital
    //                                 1.2.840.113549.1.7.3     envelopeddata
    $ZZ1 = explode('MIME-Version: 1.0',$msg);
    $ZZ2 = explode('filename="smime.p7m"',$ZZ1[count($ZZ1)-1]);
    $ZZ3 = str_replace(' ','',$ZZ2[count($ZZ2)-1]);
    $p7m_formato_der = base64_decode($ZZ3);
    $oid_hexa = self::OIDtoHex('1.2.840.113549.1.7.2');       // converte oid de texto para hexadecimal ...
    $partes = explode($oid_hexa,$p7m_formato_der);    // Faz o split pela oid...
    if(count($partes)>1)
    {
      return 'signature' ;
    }
    $oid_hexa = self::OIDtoHex('1.2.840.113549.1.7.3');
    $partes = explode($oid_hexa,$p7m_formato_der);    // Faz o split pela oid...
    if(count($partes)>1)
    {
      return 'cipher' ;
    }
    return 'normal';
  }

  private static function parseSequence($data)
  {
    $len = ord($data[1]);
    $bytes = 0;
    self::getLength($len,$bytes,$data);                  // obtem tamanho da parte de dados da oid.
    $oid_data = substr($data,2 + $bytes,$len);    // Obtem porcao de bytes pertencentes a oid.
    $ret =  self::CrlParseASN($oid_data);                 // parse dos dados da oid.
    return $ret;
  }

  private static function recuperarDadosOID($certificado_digital_formato_der, $oid)
  {
    // Esta fun��o assume que a oid esta inserida dentro de uma estrutura do tipo "sequencia" , como primeiro elemento da estrutura...
    $oid_hexa = self::OIDtoHex($oid);     // converte oid de texto para hexadecimal ...
    $partes = explode($oid_hexa,$certificado_digital_formato_der);    // Faz o split pela oid...
    $retr = array();
    if(count($partes)>1)
    {
      $partes_count = count($partes);
      for($i=1;$i<$partes_count;++$i)
      {
        //O inicio da seq pode estar a 3 ou 2 digitos antes do inicio da oid .... depende do numero de bytes usados para  tamanho da seq.
        $xcv4 = substr($partes[$i-1],strlen($partes[$i-1])-4,4); // recupera da primeira parte os 4 ultimos digitos...
        $xcv3 = substr($partes[$i-1],strlen($partes[$i-1])-3,3); // recupera da primeira parte os 3 ultimos digitos...
        $xcv2 = substr($partes[$i-1],strlen($partes[$i-1])-2,2); // recupera da primeira parte os 2 ultimos digitos...
        if($xcv2[0] == chr(0x30))
        {
          $xcv = $xcv2;
          $data = $xcv . $oid_hexa . $partes[$i];           // reconstroi a sequencia.....
          $ret = self::parseSequence($data);

          if($ret[0] != '')
          {
            $retr[] = $ret;
            continue;
          }
        }
        if($xcv3[0] == chr(0x30))
        {
          $xcv = $xcv3;
          $data = $xcv . $oid_hexa . $partes[$i];           // reconstroi a sequencia.....
          $ret = self::parseSequence($data);
          if($ret[0] != '')
          {
            $retr[] = $ret;
            continue;
          }
        }
        if($xcv4[0] == chr(0x30))
        {
          $xcv = $xcv4;
          $data = $xcv . $oid_hexa . $partes[$i];           // reconstroi a sequencia.....
          $ret = self::parseSequence($data);
          if($ret[0] != '')
          {
            $retr[] = $ret;
            continue;
          }
        }
      }
    }
    return $retr;
  }

# Recupera dados da oid passada como parametro.....
  private static function parse($oid,$valor)
  {
    //
    //  OID's PESSOA FISICA = 2.16.76.1.3.1 ,  2.16.76.1.3.6 ,  2.16.76.1.3.5 ,  2.16.76.1.4.n ... as 2.16.1.4.n n�o s�o obrigat�rias e �o sao tratadas..
    //
    //  OID's PESSOA JURIDICA = 2.16.76.1.3.4 ,  2.16.76.1.3.2 , 2.16.76.1.3.3 , 2.16.76.1.3.7
    //
    //  OID's EQUIPAMENTO/APLICA��O = 2.16.76.1.3.8 ,  2.16.76.1.3.3 , 2.16.76.1.3.2 , 2.16.76.1.3.4
    //
    //  OID  para logon no NT:  1.3.6.1.4.1.311.20.2.3
    //
    $oids = array('2.16.76.1.3.1' => array('1'=>array('NASCIMENTO',8),
        '2'=>array('CPF',11),
        '3'=>array('NIS',11),
        '4'=>array('RG',15),
        '5'=>array('ORGAOUF',6)),
        '2.16.76.1.3.2' => array('1'=>array('NOMERESPONSAVELCERTIFICADO',0)),
        '2.16.76.1.3.3' => array('1'=>array('CNPJ',14)),
        '2.16.76.1.3.4' => array('1'=>array('NASCIMENTO',8),
            '2'=>array('CPF',11),
            '3'=>array('NIS',11),
            '4'=>array('RG',15),
            '5'=>array('ORGAOUF',6)),
        '2.16.76.1.3.5' => array('1'=>array('TITULO',12),
            '2'=>array('ZONA',3),
            '3'=>array('SECAO',4),
            '4'=>array('TITULO_CIDADE_UF',0)),
        '2.16.76.1.3.6' => array('1'=>array('CADINSS',12)),
        '2.16.76.1.3.7' => array('1'=>array('CEI',12)),
        '2.16.76.1.3.8' => array('1'=>array('NOMEEMPRESARIAL',0)),
        '1.3.6.1.4.1.311.20.2.3' => array('1'=>array('NTNOMEPRINCIPAL',0)));

    $resultado = array();
    $esta_oid = $oids[$oid];
    $p = 0;
    if(is_array($esta_oid)) {
      $esta_oid_count = count($esta_oid);
      for ($i = 1; $i < $esta_oid_count + 1; ++$i) {
        if ($esta_oid[$i][1] == 0) {
          # se igual a zero, ent�o esta apontando um ultimo elemento, iniciando em $p, at� o fim dos dados
          $tamanho = strlen($valor) - $p;
        } else {
          $tamanho = $esta_oid[$i][1];
        }
        $resultado[$oid][$esta_oid[$i][0]] = substr($valor, $p, $tamanho);
        // A linha logo abaixo he para manter compatibilidade com versoes anteriores... Sera desativada assim que possivel...
        $resultado[$esta_oid[$i][0]] = substr($valor, $p, $tamanho);
        $p = $p + $esta_oid[$i][1];
      }
    }

    return $resultado;
  }

  private static function subjectAltName($xx, $certificado_digital_formato_der)
  {
    $dados = array();
    $ret = self::recuperarDadosOID($certificado_digital_formato_der,'2.5.29.17');
    if(count($ret))
    {
      if(substr($ret[0][1][0],0,12) == 'octet string')
      {
        $ret = $ret[0][1][1][1];
      }
      else
      {
        $ret = $ret[0][2][1][1];  // Se n�o iniciou por um octet string skipa para o pr�ximo item na estrutura.
      }
      foreach($ret as $group)
      {
        if(substr($group[0],0,17) == 'Context Especific')  // primeiro indice tem de ter o valor  'Context Especific' ...
        {
          $oid = explode(':',$group[1][0][0][0]);   //  Pega o numero da oid.
          $dados = array_merge(self::parse(trim($oid[1]), $group[1][0][1][1][0][1]),$dados); // Passa a oid e o seu valor para ser parseado....
        }
        if(substr($group[0],0,6) == 'string')
        {
          if(strpos($group[1],'@'))                  //se he email tem de ter uma @.
          {
            $aux_email = $group[1];
          }
        }
      }
      // O  EMAIL foi localizado no loop de tratamento das OIDs.....
      $dados['EMAIL'] = $aux_email;
    }
    return $dados;
  }

  private static function CRLDistributionPoints($xx, $certificado_digital_formato_der)
  {
    $AUX = self::recuperarDadosOID($certificado_digital_formato_der,'2.5.29.31');
    $i=1;
    if(substr($AUX[0][1][0],0,7) == 'boolean')
    {
      $i=2;
    }
    $ret = array();
    if($AUX[0][$i][1][1])
    {
      if(is_array($AUX[0][$i][1][1]))
      {
        //Pode existir mais de um local para obter a CRL.
        foreach($AUX[0][$i][1][1] as $crl)
        {
          if(substr($crl[1][0][1][0][1][0][1],0,4) == 'http' || substr($crl[1][0][1][0][1][0][1],0,4) == 'ldap')
          {
            $ret[] = $crl[1][0][1][0][1][0][1];
          }
        }
      }
    }

    // Se $ret esta vazio tenta obter crls em outra estrutura(outro layout).
    if(count($ret) == 0)
    {
      if(is_array($AUX[0][1][1][1][0][1][0][1][0][1][0]))
      {
        //Pode existir mais de um local para obter a CRL.
        foreach($AUX[0][1][1][1][0][1][0][1][0][1][0]as $crl)
        {
          if(substr($crl[1],0,4) == 'http' || substr($crl[1],0,4) == 'ldap')
          {
            $ret[] = $crl[1];
          }
        }
      }
    }

    return array('CRLDISTRIBUTIONPOINTS' => $ret);
  }

  private static function SERIALNUMBER($cert_data,$KK)
  {
    $dados = array();
    if($cert_data[1][0][1][$KK][1])
    {
      $dados['SERIALNUMBER'] = $cert_data[1][0][1][$KK][1];
    }
    return $dados;
  }

  private static function SUBJECT($cert_data,$KK)
  {
    $dados = array();
    $dados['SUBJECT'] = array();
    foreach($cert_data[1][0][1][$KK][1] as $AUX2)
    {
      $dados['SUBJECT'][trim($AUX2[1][1][0][1])] = $AUX2[1][1][1][1];
    }
    $AUX = explode(':',$dados['SUBJECT']['CN']);
    $dados['NOME'] = $AUX[0];
    return $dados;
  }

  private static function ISSUER($cert_data,$KK)
  {
    $dados = array();
    $dados['EMISSOR_CAMINHO_COMPLETO']  = array();

    foreach($cert_data[1][0][1][$KK][1] as $AUX2)
    {
      $dados['EMISSOR_CAMINHO_COMPLETO'][$AUX2[1][1][0][1]] = $AUX2[1][1][1][1] ;
    }
    $dados['EMISSOR'] = $dados['EMISSOR_CAMINHO_COMPLETO']['CN'];
    return $dados;
  }

  private static function BEFOREAFTER($cert_data,$KK)
  {
    $dados = array();
    $dados['INICIO_VALIDADE'] = self::gerarDataHora($cert_data[1][0][1][$KK][1][0][1]);
    $dados['FIM_VALIDADE'] = self::gerarDataHora($cert_data[1][0][1][$KK][1][1][1]);
    $agora = date('YmdHis');
    if(($agora < $dados['INICIO_VALIDADE']) || ($agora > $dados['FIM_VALIDADE']))
    {
      $dados['EXPIRADO'] = true;
    }
    else
    {
      $dados['EXPIRADO'] = false;
    }
    return $dados;
  }

  private static function AUTHORITYKEYIDENTIFIER($xx, $certificado_digital_formato_der)
  {
    $dados = array();
    if (isset($certificado_digital_formato_der))
    {
      $caid = self::recuperarDadosOID($certificado_digital_formato_der,'2.5.29.35');
      $i=1;
      if(substr($caid[0][1][0],0,7) == 'boolean')
      {
        $i=2;
      }
      $dados['AUTHORITYKEYIDENTIFIER'] = $caid[0][$i][1][1][0][1];
    }
    else
    {
      // Se nao existir um valor, assume certificado auto assinado .....
      $dados['AUTHORITYKEYIDENTIFIER'] = "auto-assinado";
    }
    return $dados;
  }

  private static function KEYUSAGE($xx, $certificado_digital_formato_der)
  {
    $KeyUsage= array( 0x80 => 'digitalSignature',
        0x40 => 'nonRepudiation',
        0x20 => 'keyEncipherment',
        0x10 => 'dataEncipherment',
        0x08 => 'keyAgreement',
        0x04 => 'keyCertSign',
        0x02 => 'cRLSign');

    $dados = array();
    if (isset($certificado_digital_formato_der))
    {
      $AUX = self::recuperarDadosOID($certificado_digital_formato_der,'2.5.29.15');  // busca oid do keyusage
      $AUX = explode(':',$AUX[0][2][1][1]);
      if(count($AUX) == 3)
      {
        foreach($KeyUsage as $chave => $valor)
        {
          if($AUX[2] & $chave)
          {
            $dados['KEYUSAGE'][$valor] = TRUE;
          }
        }
      }
    }
    return $dados;
  }

  private static function EXTKEYUSAGE($xx, $certificado_digital_formato_der)
  {
    $dados = array();
    if (isset($certificado_digital_formato_der))
    {
      $AUX = self::recuperarDadosOID($certificado_digital_formato_der,'2.5.29.37');  // busca oid do extkeyusage
      $AUX1 = $AUX[0][count($AUX[0])-1];
      if(count($AUX1) > 0)
      {
        foreach($AUX1[1][1] as $itens)
        {
          $AUX2 = explode(':',$itens[0]);
          $dados['EXTKEYUSAGE'][trim($itens[1])] =  trim($AUX2[1]);
        }
      }
    }
    return $dados;
  }

  private static function BASICCONSTRAINTS($xx, $certificado_digital_formato_der)
  {
    $dados = array();
    if (isset($certificado_digital_formato_der))
    {
      $AUX = self::recuperarDadosOID($certificado_digital_formato_der,'2.5.29.19');  // busca oid do BasicConstraints
      if(count($AUX) > 0)
      {
        $dados['CA'] = $AUX[0][count($AUX[0])-1][1][1][0][1];
      }
    }
    return $dados;
  }

  public static function recuperarDados($certificado_digital_formato_pem)
  {
    $cert_der =  self::pem2der($certificado_digital_formato_pem);

    $dados = self::recuperarDadosDER($cert_der);

    return $dados;
  }

  private static function recuperarDadosDER($cert_der){

    $funcoes = array('SERIALNUMBER' => 1,
        'ISSUER' => 3,
        'BEFOREAFTER' => 4,
        'SUBJECT' => 5,
        'AUTHORITYKEYIDENTIFIER' => $cert_der,
        'KEYUSAGE' => $cert_der,
        'EXTKEYUSAGE' => $cert_der,
        'BASICCONSTRAINTS' => $cert_der,
        'CRLDistributionPoints' =>  $cert_der,
        'subjectAltName' => $cert_der);

    $dados=array();

    $cert_data = self::CrlParseASN( $cert_der);

    foreach($funcoes as $funcao => $parametro)
    {
      $dados = array_merge($dados, call_user_func(array('InfraCertificadoDigital', $funcao), $cert_data, $parametro));
    }

    if(!$dados['EMAIL'])
    {
      if(isset( $dados['SUBJECT']['emailAddress']))
      {
        $dados['EMAIL'] = $dados['SUBJECT']['emailAddress'];
      }
    }
    return $dados;
  }
}
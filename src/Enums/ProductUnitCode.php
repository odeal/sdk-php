<?php

declare(strict_types=1);

namespace Odeal\Sdk\Enums;

/**
 * Birim Kodları.
    /// - _3I: Kilogram-Adet (C# Uyumu için ön ekli)
 */
enum ProductUnitCode: string
{
    /**
     * API Değeri: C62
     */
    case C62 = 'C62';
    /**
     * API Değeri: CTM
     */
    case CTM = 'CTM';
    /**
     * API Değeri: GRM
     */
    case GRM = 'GRM';
    /**
     * API Değeri: GT
     */
    case GT = 'GT';
    /**
     * API Değeri: MND
     */
    case MND = 'MND';
    /**
     * API Değeri: KGM
     */
    case KGM = 'KGM';
    /**
     * API Değeri: LTR
     */
    case LTR = 'LTR';
    /**
     * API Değeri: MTK
     */
    case MTK = 'MTK';
    /**
     * API Değeri: KWH
     */
    case KWH = 'KWH';
    /**
     * API Değeri: MTQ
     */
    case MTQ = 'MTQ';
    /**
     * API Değeri: MTR
     */
    case MTR = 'MTR';
    /**
     * API Değeri: CMT
     */
    case CMT = 'CMT';
    /**
     * API Değeri: B32
     */
    case B32 = 'B32';
    /**
     * API Değeri: CCT
     */
    case CCT = 'CCT';
    /**
     * API Değeri: PR
     */
    case PR = 'PR';
    /**
     * API Değeri: D30
     */
    case D30 = 'D30';
    /**
     * API Değeri: GFI
     */
    case GFI = 'GFI';
    /**
     * API Değeri: KPO
     */
    case KPO = 'KPO';
    /**
     * API Değeri: 3I
     */
    case _3I = '3I';
    /**
     * API Değeri: KFO
     */
    case KFO = 'KFO';
    /**
     * API Değeri: KHY
     */
    case KHY = 'KHY';
    /**
     * API Değeri: KMA
     */
    case KMA = 'KMA';
    /**
     * API Değeri: KNI
     */
    case KNI = 'KNI';
    /**
     * API Değeri: KPH
     */
    case KPH = 'KPH';
    /**
     * API Değeri: KSH
     */
    case KSH = 'KSH';
    /**
     * API Değeri: KUR
     */
    case KUR = 'KUR';
    /**
     * API Değeri: D32
     */
    case D32 = 'D32';
    /**
     * API Değeri: GWH
     */
    case GWH = 'GWH';
    /**
     * API Değeri: MWH
     */
    case MWH = 'MWH';
    /**
     * API Değeri: KWT
     */
    case KWT = 'KWT';
    /**
     * API Değeri: LPA
     */
    case LPA = 'LPA';
    /**
     * API Değeri: DMK
     */
    case DMK = 'DMK';
    /**
     * API Değeri: NCL
     */
    case NCL = 'NCL';
    /**
     * API Değeri: SM3
     */
    case SM3 = 'SM3';
    /**
     * API Değeri: R9
     */
    case R9 = 'R9';
    /**
     * API Değeri: SET
     */
    case SET = 'SET';
    /**
     * API Değeri: T3
     */
    case T3 = 'T3';
    /**
     * API Değeri: AD
     */
    case AD = 'AD';
    /**
     * API Değeri: PA
     */
    case PA = 'PA';
    /**
     * API Değeri: PK
     */
    case PK = 'PK';

    /**
     * Tüm değerleri array olarak döner.
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
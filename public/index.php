<?php

declare(strict_types=1);

use Bakame\Shoes\AdultSize;
use Bakame\Shoes\AdultUnit;
use Bakame\Shoes\ChildUnit;
use Bakame\Shoes\LengthRange;
use Bakame\Shoes\LengthUnit;
use Bakame\Shoes\ShoeException;
use Bakame\Shoes\ShoeSize;
use Bakame\Shoes\ShoeType;
use Bakame\Shoes\ShoeUnit;

require __DIR__.'/../vendor/autoload.php';

const DEFAULT_LOCALE = 'en';

const TRANSLATIONS = [
    'en' => [
        'name' => 'English',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => 'System theme',
            'Light theme' => 'Light theme',
            'Dark theme' => 'Dark theme',
            'Size' => 'Size',
            'Decrease shoe size' => 'Decrease shoe size',
            'Increase shoe size' => 'Increase shoe size',
            'From' => 'From',
            'To' => 'To',
            'Swap source and target units' => 'Swap source and target units',
            'Convert' => 'Convert',
            'Toggle dark mode' => 'Toggle dark mode',
            'Unable to contact the shoe-size converter.' => 'Unable to contact the shoe-size converter.',
            'Result' => 'Result',
            'The request body is invalid or does not meet business rules.' => 'The request body is invalid or does not meet business rules.',
            'No matching shoe size found.' => 'No matching shoe size found.',
            'The input size cannot be converted into another unit system.' => 'The input size cannot be converted into another unit system.',
            'Unable to determine the foot length' => 'Unable to determine the foot length.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "Only the 'GET' method is allowed when requesting a JSON response.",
            'Language' => 'Language',
            'Shoe Size Converter' => 'Shoe Size Converter',
            'N/A' => 'N/A',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard.",
            'Size type' => 'Size Type',
            'Adults' => 'Adults',
            'Children' => 'Children',
            'Last length range:' => 'Last length range:',
        ],
    ],
    'fr' => [
        'name' => 'Français',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => 'Thème du système',
            'Light theme' => 'Thème clair',
            'Dark theme' => 'Thème sombre',
            'Size' => 'Pointure',
            'Decrease shoe size' => 'Diminuer la pointure',
            'Increase shoe size' => 'Augmenter la pointure',
            'From' => 'De',
            'To' => 'À',
            'Swap source and target units' => 'Inverser les unités source et cible',
            'Convert' => 'Convertir',
            'Toggle dark mode' => 'Activer/désactiver le mode sombre',
            'Unable to contact the shoe-size converter.' => 'Impossible de contacter le service de conversion.',
            'Result' => 'Résultat',
            'The request body is invalid or does not meet business rules.' => 'La requête n\'est pas valide ou ne respecte pas les règles métier.',
            'No matching shoe size found.' => 'Aucune pointure trouvée.',
            'The input size cannot be converted into another unit system.' => 'La pointure saisie ne peut être convertie dans un autre système d\'unité de pointure.',
            'Unable to determine the foot length' => 'La longueur du pied ne peut être déterminée.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "Seule la méthode HTTP 'GET' est permise pour obtenir une réponse en JSON.",
            'Language' => 'Langue',
            'Shoe Size Converter' => 'Convertisseur de pointure',
            'N/A' => 'N/D',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'Convertissez les pointures entre les systèmes EU, UK, US homme, US femme et Mondopoint avec Shoe Wizard.',
            'Size type' => 'Type de pointure',
            'Adults' => 'Adultes',
            'Children' => 'Enfants',
            'Last length range:' => 'Plage de longueur de la forme :',
        ],
    ],
    'es' => [
        'name' => 'Español',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => 'Tema del sistema',
            'Light theme' => 'Tema claro',
            'Dark theme' => 'Tema oscuro',
            'Size' => 'Talla',
            'Decrease shoe size' => 'Disminuir la talla',
            'Increase shoe size' => 'Aumentar la talla',
            'From' => 'De',
            'To' => 'A',
            'Swap source and target units' => 'Invertir las unidades de origen y destino',
            'Convert' => 'Convertir',
            'Toggle dark mode' => 'Activar/desactivar el modo oscuro',
            'Unable to contact the shoe-size converter.' => 'No se puede contactar con el servicio de conversión.',
            'Result' => 'Resultado',
            'The request body is invalid or does not meet business rules.' => 'El cuerpo de la solicitud no es válido o no cumple las reglas de negocio.',
            'No matching shoe size found.' => 'No se ha encontrado ninguna talla de calzado correspondiente.',
            'The input size cannot be converted into another unit system.' => 'La talla introducida no se puede convertir a otro sistema de tallas.',
            'Unable to determine the foot length' => 'No se puede determinar la longitud del pie.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "Solo se permite el método HTTP 'GET' para obtener una respuesta JSON.",
            'Language' => 'Idioma',
            'Shoe Size Converter' => 'Convertidor de tallas de calzado',
            'N/A' => 'N/D',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'Convierte tallas de calzado entre los sistemas EU, UK, US hombre, US mujer y Mondopoint con Shoe Wizard.',
            'Size type' => 'Tipo de talla',
            'Adults' => 'Adultos',
            'Children' => 'Niños',
            'Last length range:' => 'Rango de longitud de la horma:',
        ],
    ],
    'pt' => [
        'name' => 'Português',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => 'Tema do sistema',
            'Light theme' => 'Tema claro',
            'Dark theme' => 'Tema escuro',
            'Size' => 'Tamanho do calçado',
            'Decrease shoe size' => 'Diminuir o tamanho do calçado',
            'Increase shoe size' => 'Aumentar o tamanho do calçado',
            'From' => 'De',
            'To' => 'Para',
            'Swap source and target units' => 'Trocar as unidades de origem e destino',
            'Convert' => 'Converter',
            'Toggle dark mode' => 'Ativar/desativar o modo escuro',
            'Unable to contact the shoe-size converter.' => 'Não foi possível contactar o serviço de conversão de tamanhos de calçado.',
            'Result' => 'Resultado',
            'The request body is invalid or does not meet business rules.' => 'O conteúdo do pedido é inválido ou não cumpre as regras de negócio.',
            'No matching shoe size found.' => 'Não foi encontrado nenhum tamanho de calçado correspondente.',
            'The input size cannot be converted into another unit system.' => 'O tamanho de calçado introduzido não pode ser convertido para outro sistema de tamanhos.',
            'Unable to determine the foot length' => 'Não foi possível determinar o comprimento do pé.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "Apenas o método HTTP 'GET' é permitido ao solicitar uma resposta JSON.",
            'Language' => 'Idioma',
            'Shoe Size Converter' => 'Conversor de tamanhos de calçado',
            'N/A' => 'N/D',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'Converta tamanhos de calçado entre os sistemas EU, UK, US masculino, US feminino e Mondopoint com o Shoe Wizard.',
            'Size type' => 'Tipo de tamanho',
            'Adults' => 'Adultos',
            'Children' => 'Crianças',
            'Last length range:' => 'Intervalo de comprimento da forma:',
        ],
    ],
    'it' => [
        'name' => 'Italiano',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => 'Tema di sistema',
            'Light theme' => 'Tema chiaro',
            'Dark theme' => 'Tema scuro',
            'Size' => 'Numero',
            'Decrease shoe size' => 'Diminuire il numero',
            'Increase shoe size' => 'Aumentare il numero',
            'From' => 'Da',
            'To' => 'A',
            'Swap source and target units' => 'Invertire le unità di origine e di destinazione',
            'Convert' => 'Converti',
            'Toggle dark mode' => 'Attiva/disattiva la modalità scura',
            'Unable to contact the shoe-size converter.' => 'Impossibile contattare il servizio di conversione.',
            'Result' => 'Risultato',
            'The request body is invalid or does not meet business rules.' => 'Il corpo della richiesta non è valido o non rispetta le regole di business.',
            'No matching shoe size found.' => 'Nessun numero di scarpe corrispondente trovato.',
            'The input size cannot be converted into another unit system.' => 'Il numero inserito non può essere convertito in un altro sistema di numerazione delle scarpe.',
            'Unable to determine the foot length' => 'Impossibile determinare la lunghezza del piede.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "È consentito solo il metodo HTTP 'GET' per ottenere una risposta JSON.",
            'Language' => 'Lingua',
            'Shoe Size Converter' => 'Convertitore di taglie di scarpe',
            'N/A' => 'N/D',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'Converti le taglie di scarpe tra i sistemi EU, UK, US uomo, US donna e Mondopoint con Shoe Wizard.',
            'Size type' => 'Tipo di taglia',
            'Adults' => 'Adulti',
            'Children' => 'Bambini',
            'Last length range:' => 'Intervallo di lunghezza della forma:',
        ],
    ],
    'nl' => [
        'name' => 'Nederlands',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => 'Systeemthema',
            'Light theme' => 'Licht thema',
            'Dark theme' => 'Donker thema',
            'Size' => 'Schoenmaat',
            'Decrease shoe size' => 'Schoenmaat verlagen',
            'Increase shoe size' => 'Schoenmaat verhogen',
            'From' => 'Van',
            'To' => 'Naar',
            'Swap source and target units' => 'Bron- en doeleenheden omwisselen',
            'Convert' => 'Omrekenen',
            'Toggle dark mode' => 'Donkere modus in-/uitschakelen',
            'Unable to contact the shoe-size converter.' => 'Kan geen verbinding maken met de schoenmaatconversieservice.',
            'Result' => 'Resultaat',
            'The request body is invalid or does not meet business rules.' => 'De inhoud van het verzoek is ongeldig of voldoet niet aan de bedrijfsregels.',
            'No matching shoe size found.' => 'Geen overeenkomende schoenmaat gevonden.',
            'The input size cannot be converted into another unit system.' => 'De ingevoerde schoenmaat kan niet naar een ander maatsysteem worden omgerekend.',
            'Unable to determine the foot length' => 'De voetlengte kan niet worden bepaald.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "Alleen de HTTP-methode 'GET' is toegestaan voor het opvragen van een JSON-antwoord.",
            'Language' => 'Taal',
            'Shoe Size Converter' => 'Omrekenaar voor schoenmaten',
            'N/A' => 'N/B',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'Zet schoenmaten om tussen EU-, UK-, US-heren-, US-dames- en Mondopoint-maten met Shoe Wizard.',
            'Size type' => 'Maattype',
            'Adults' => 'Volwassenen',
            'Children' => 'Kinderen',
            'Last length range:' => 'Lengtebereik van de leest:',
        ],
    ],
    'de' => [
        'name' => 'Deutsch',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => 'Systemdesign',
            'Light theme' => 'Helles Design',
            'Dark theme' => 'Dunkles Design',
            'Size' => 'Schuhgröße',
            'Decrease shoe size' => 'Schuhgröße verkleinern',
            'Increase shoe size' => 'Schuhgröße vergrößern',
            'From' => 'Von',
            'To' => 'Nach',
            'Swap source and target units' => 'Quell- und Zieleinheiten vertauschen',
            'Convert' => 'Umrechnen',
            'Toggle dark mode' => 'Dunkelmodus ein-/ausschalten',
            'Unable to contact the shoe-size converter.' => 'Der Schuhgrößen-Umrechnungsdienst ist nicht erreichbar.',
            'Result' => 'Ergebnis',
            'The request body is invalid or does not meet business rules.' => 'Der Anfrageinhalt ist ungültig oder entspricht nicht den Geschäftsregeln.',
            'No matching shoe size found.' => 'Keine passende Schuhgröße gefunden.',
            'The input size cannot be converted into another unit system.' => 'Die eingegebene Schuhgröße kann nicht in ein anderes Größensystem umgerechnet werden.',
            'Unable to determine the foot length' => 'Die Fußlänge kann nicht ermittelt werden.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "Für eine JSON-Antwort ist nur die HTTP-Methode 'GET' zulässig.",
            'Language' => 'Sprache',
            'Shoe Size Converter' => 'Schuhgrößen-Umrechner',
            'N/A' => 'N/V',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'Konvertieren Sie Schuhgrößen zwischen den Systemen EU, UK, US Herren, US Damen und Mondopoint mit Shoe Wizard.',
            'Size type' => 'Größentyp',
            'Adults' => 'Erwachsene',
            'Children' => 'Kinder',
            'Last length range:' => 'Längenbereich des Leisten:',
        ],
    ],
    'ar' => [
        'name' => 'العربية',
        'direction' => 'rtl',
        'messages' => [
            'System theme' => 'مظهر النظام',
            'Light theme' => 'المظهر الفاتح',
            'Dark theme' => 'المظهر الداكن',
            'Size' => 'مقاس الحذاء',
            'Decrease shoe size' => 'خفض مقاس الحذاء',
            'Increase shoe size' => 'زيادة مقاس الحذاء',
            'From' => 'من',
            'To' => 'إلى',
            'Swap source and target units' => 'تبديل وحدات المصدر والهدف',
            'Convert' => 'تحويل',
            'Toggle dark mode' => 'تفعيل/تعطيل الوضع الداكن',
            'Unable to contact the shoe-size converter.' => 'تعذر الاتصال بخدمة تحويل مقاسات الأحذية.',
            'Result' => 'النتيجة',
            'The request body is invalid or does not meet business rules.' => 'محتوى الطلب غير صالح أو لا يستوفي قواعد العمل.',
            'No matching shoe size found.' => 'لم يتم العثور على مقاس حذاء مطابق.',
            'The input size cannot be converted into another unit system.' => 'لا يمكن تحويل مقاس الحذاء المُدخل إلى نظام مقاسات آخر.',
            'Unable to determine the foot length' => 'تعذر تحديد طول القدم.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "لا يُسمح إلا بطريقة HTTP 'GET' عند طلب استجابة بتنسيق JSON.",
            'Language' => 'اللغة',
            'Shoe Size Converter' => 'محول مقاسات الأحذية',
            'N/A' => 'غير متاح',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'حوّل مقاسات الأحذية بين أنظمة المقاسات الأوروبية والبريطانية والأمريكية للرجال والنساء وموندوبوانت باستخدام Shoe Wizard.',
            'Size type' => 'نوع المقاس',
            'Adults' => 'بالغون',
            'Children' => 'أطفال',
            'Last length range:' => 'نطاق طول القالب:',
        ],
    ],
    'zh' => [
        'name' => '简体中文',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => '系统主题',
            'Light theme' => '浅色主题',
            'Dark theme' => '深色主题',
            'Size' => '鞋码',
            'Decrease shoe size' => '减小鞋码',
            'Increase shoe size' => '增大鞋码',
            'From' => '从',
            'To' => '到',
            'Swap source and target units' => '交换源和目标单位',
            'Convert' => '转换',
            'Toggle dark mode' => '切换深色模式',
            'Unable to contact the shoe-size converter.' => '无法连接鞋码转换服务。',
            'Result' => '结果',
            'The request body is invalid or does not meet business rules.' => '请求内容无效或不符合业务规则。',
            'No matching shoe size found.' => '未找到匹配的鞋码。',
            'The input size cannot be converted into another unit system.' => '输入的鞋码无法转换为其他尺码体系。',
            'Unable to determine the foot length' => '无法确定脚长。',
            "Only the 'GET' method is allowed when requesting a JSON response." => "请求 JSON 响应时，仅允许使用 HTTP 'GET' 方法。",
            'Language' => '语言',
            'Shoe Size Converter' => '鞋码转换器',
            'N/A' => '不适用',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => '使用 Shoe Wizard 在欧码、英码、美国男鞋码、美国女鞋码和 Mondopoint 之间转换鞋码。',
            'Size type' => '尺码类型',
            'Adults' => '成人',
            'Children' => '儿童',
            'Last length range:' => '鞋楦长度范围：',
        ],
    ],
    'ko' => [
        'name' => '한국어',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => '시스템 테마',
            'Light theme' => '밝은 테마',
            'Dark theme' => '어두운 테마',
            'Size' => '신발 사이즈',
            'Decrease shoe size' => '신발 사이즈 줄이기',
            'Increase shoe size' => '신발 사이즈 늘리기',
            'From' => '변환 전',
            'To' => '변환 후',
            'Swap source and target units' => '변환 전후 단위 바꾸기',
            'Convert' => '변환',
            'Toggle dark mode' => '다크 모드 켜기/끄기',
            'Unable to contact the shoe-size converter.' => '신발 사이즈 변환 서비스에 연결할 수 없습니다.',
            'Result' => '결과',
            'The request body is invalid or does not meet business rules.' => '요청 내용이 올바르지 않거나 비즈니스 규칙을 충족하지 않습니다.',
            'No matching shoe size found.' => '일치하는 신발 사이즈를 찾을 수 없습니다.',
            'The input size cannot be converted into another unit system.' => '입력한 신발 사이즈를 다른 사이즈 체계로 변환할 수 없습니다.',
            'Unable to determine the foot length' => '발 길이를 확인할 수 없습니다.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "JSON 응답을 요청할 때는 HTTP 'GET' 메서드만 사용할 수 있습니다.",
            'Language' => '언어',
            'Shoe Size Converter' => '신발 사이즈 변환기',
            'N/A' => '해당 없음',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'Shoe Wizard를 사용하여 EU, UK, 미국 남성, 미국 여성 및 Mondopoint 신발 사이즈를 변환하세요.',
            'Size type' => '사이즈 유형',
            'Adults' => '성인',
            'Children' => '아동',
            'Last length range:' => '라스트 길이 범위:',
        ],
    ],
    'sw' => [
        'name' => 'Kiswahili',
        'direction' => 'ltr',
        'messages' => [
            'System theme' => 'Mandhari ya mfumo',
            'Light theme' => 'Mandhari nyepesi',
            'Dark theme' => 'Mandhari meusi',
            'Size' => 'Ukubwa wa kiatu',
            'Decrease shoe size' => 'Punguza ukubwa wa kiatu',
            'Increase shoe size' => 'Ongeza ukubwa wa kiatu',
            'From' => 'Kutoka',
            'To' => 'Kwenda',
            'Swap source and target units' => 'Badilisha vipimo vya chanzo na lengwa',
            'Convert' => 'Badilisha',
            'Toggle dark mode' => 'Washa/zima hali ya giza',
            'Unable to contact the shoe-size converter.' => 'Imeshindikana kuwasiliana na huduma ya kubadilisha ukubwa wa viatu.',
            'Result' => 'Matokeo',
            'The request body is invalid or does not meet business rules.' => 'Maudhui ya ombi si sahihi au hayakidhi masharti ya huduma.',
            'No matching shoe size found.' => 'Hakuna ukubwa wa kiatu unaolingana uliopatikana.',
            'The input size cannot be converted into another unit system.' => 'Ukubwa wa kiatu uliowekwa hauwezi kubadilishwa kuwa mfumo mwingine wa vipimo.',
            'Unable to determine the foot length' => 'Imeshindikana kubaini urefu wa mguu.',
            "Only the 'GET' method is allowed when requesting a JSON response." => "Ni njia ya HTTP 'GET' pekee inayoruhusiwa wakati wa kuomba jibu la JSON.",
            'Language' => 'Lugha',
            'Shoe Size Converter' => 'Kibadilisha Ukubwa wa Viatu',
            'N/A' => 'Haipatikani',
            "Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard." => 'Badilisha ukubwa wa viatu kati ya mifumo ya EU, UK, Marekani kwa wanaume, Marekani kwa wanawake na Mondopoint kwa kutumia Shoe Wizard.',
            'Size type' => 'Aina ya saizi',
            'Adults' => 'Watu wazima',
            'Children' => 'Watoto',
            'Last length range:' => 'Masafa ya urefu wa kalibu:',
        ],
    ],
];

/**
 * @throws RuntimeException|ShoeException
 *
 * @return array{
 *     source: string,
 *     result: ?array{
 *         unit: string,
 *         value: int|float
 *     },
 *     measurements: array{
 *         centimeters: float,
 *         inches: float
 *     },
 *     lastLengthRange: ?array{
 *         centimeters: array{
 *             min: float,
 *             max: float
 *         },
 *         inches: array{
 *             min: float,
 *             max: float
 *         },
 *     }
 * }
 */
function convert(ShoeSize $shoeSize, ShoeUnit $to, ShoeType $unitType): array
{
    $shoeSize->unit::class === $to::class || throw new RuntimeException('Adult and Child shoes size can not be mixed.');
    $converter = $unitType->converter();
    $source = 'ISO 19407:2023-based';
    $hasResults = static fn ($value): bool => null !== $value;
    $equivalents = $converter->equivalents($shoeSize);
    $results = array_filter($equivalents, $hasResults);
    if ([] === $results && $shoeSize instanceof AdultSize) {
        $source = 'calculated';
        $equivalents = $shoeSize->equivalents();
        $results = array_filter($equivalents, $hasResults);
    }

    [] !== $results || throw new RuntimeException('No matching shoe size found.');
    /** @var ?ShoeSize $cm */
    $cm = $equivalents['cm'] ?? null;
    null !== $cm || throw new RuntimeException('Unable to determine the foot length');

    /** @var ?ShoeSize $result */
    $result = $equivalents[$to->value] ?? null;
    if (null !== $result) {
        $result = ['unit' => $result->unit->value, 'value' => $result->size];
    }

    $ranges = null;
    $lastLengthRange = $converter->lastLengthRange($cm);
    if ($lastLengthRange instanceof LengthRange) {
        $range = $converter->lastLengthRange($cm);
        if ($range instanceof LengthRange) {
            $ranges = [
                'centimeters' => ['min' => $range->min->in(LengthUnit::Centimeter), 'max' => $range->max->in(LengthUnit::Centimeter)],
                'inches' => ['min' => $range->min->in(LengthUnit::Inch), 'max' => $range->max->in(LengthUnit::Inch)],
            ];
        }
    }

    $footLength = $converter->footLength($cm);

    return [
        'source' => $source,
        'result' => $result,
        'measurements' => [
            'centimeters' => $footLength->in(LengthUnit::Centimeter),
            'inches' => $footLength->in(LengthUnit::Inch),
        ],
        'lastLengthRange' => $ranges,
    ];
}

/**
 * @param array<non-empty-string, string> $messages
 * @param non-negative-int $status
 * @param non-empty-string $locale
 *
 * @throws JsonException
 */
function problem(array $messages, int $status, string $locale): never
{
    $detail = t('The request body is invalid or does not meet business rules.', $locale);
    $errors = [];
    foreach ($messages as $field => $content) {
        $errors[] = ['field' => $field, 'message' => $content];
        if ('convert' === $field) {
            $detail = t('The application is unable to process your request.', $locale);
        }
    }

    http_response_code($status);
    header('Content-Type: application/problem+json; charset=UTF-8');
    echo j([
        'type' => 'about:blank',
        'title' => match ($status) {
            400 => 'Bad Request',
            404 => 'Not Found',
            422 => 'Unprocessable content',
            405 => 'Method Not Allowed',
            429 => 'Too Many Requests',
            default => 'Internal Server Error',
        },
        'status' => $status,
        'detail' => $detail,
        'instance' => $_SERVER['REQUEST_URI'],
        'errors' => $errors,
    ]);
    exit;
}

function e(string|float|int|null|Stringable $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * @param non-empty-string $locale
 */
function t(string $key, string $locale): string
{
    return TRANSLATIONS[$locale]['messages'][$key] ?? $key;
}

/**
 * @param non-empty-string $locale
 */
function et(string $key, string $locale): string
{
    return e(t($key, $locale));
}

/**
 * @throws JsonException
 */
function j(mixed $value): string
{
    return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}

function server(string $key, string $default = ''): string
{
    $value = $_SERVER[$key] ?? $default;

    return is_string($value) ? trim($value) : $default;
}

function cookie(string $key, string $default = ''): string
{
    $value = $_COOKIE[$key] ?? $default;

    return is_string($value) ? trim($value) : $default;
}

function query(string $key, string $default = ''): string
{
    $value = $_GET[$key] ?? $default;

    return is_string($value) ? trim($value) : $default;
}

function hasQueryValues(string ...$keys): bool
{
    return [] !== $keys && array_all($keys, static fn (string $key): bool => '' !== query($key));
}

/**
 * @return non-empty-string
 */
function resolveLocale(): string
{
    $locale = query('lang');
    if ('' === $locale) {
        $locale = cookie('locale');
    }

    if ('' !== $locale) {
        $locale = strtolower($locale);
    }

    $supported = array_keys(TRANSLATIONS);
    if (in_array($locale, $supported, true)) {
        return $locale;
    }

    foreach (parseAcceptHeader(server('HTTP_ACCEPT_LANGUAGE')) as $item) {
        $locale = strtolower(explode('-', $item['value'])[0]);
        if (in_array($locale, $supported, true)) {
            return $locale;
        }
    }

    return DEFAULT_LOCALE;
}

function requestsJson(): bool
{
    foreach (parseAcceptHeader(server('HTTP_ACCEPT')) as $item) {
        if ('application/json' === $item['value']) {
            return true;
        }

        if (
            'text/javascript' === $item['value'] ||
            'text/csv' === $item['value'] ||
            'text/html' === $item['value'] ||
            'application/xhtml+xml' === $item['value'] ||
            'application/xml' === $item['value']
        ) {
            return false;
        }
    }

    return false;
}

/**
 * @return list<array{value: string, quality: float, position: int}>
 */
function parseAcceptHeader(string $header): array
{
    $items = [];
    $position = 0;

    foreach (explode(',', $header) as $part) {
        $part = trim($part);
        if ('' === $part) {
            continue;
        }

        [$value, $parameters] = explode(';', $part, 2) + ['', null];
        $value = trim($value);
        $quality = 1.0;
        if (
            null !== $parameters
            && 1 === preg_match('/(?:^|;)\s*q\s*=\s*(0(?:\.\d+)?|1(?:\.0+)?)\s*(?:;|$)/i', $parameters, $matches)
        ) {
            $quality = (float) $matches[1];
        }

        if ($quality <= 0) {
            continue;
        }

        $items[] = ['value' => $value, 'quality' => $quality, 'position' => $position++];
    }

    usort(
        $items,
        static fn (array $a, array $b): int =>
        0 !== ($comparison = $b['quality'] <=> $a['quality'])
            ? $comparison
            : $a['position'] <=> $b['position']
    );

    return $items;
}

//! Script starts here

$isJsonRequest = requestsJson();
$requestedLocale = query('lang');
$locale = resolveLocale();
$unitType = ShoeType::tryFrom(query('type')) ?? ShoeType::Adults;
$queryString = http_build_query(array_filter([
    'type' => $unitType->value,
    'unit' => query('unit'),
    'size' => query('size'),
    'to' => query('to'),
], fn (string $value): bool => '' !== $value));
$location = server('SCRIPT_NAME', 'index.php').('' !== $queryString ? '?'.$queryString : '');

if ('GET' !== server('REQUEST_METHOD', 'GET')) {
    if ($isJsonRequest) {
        problem(['convert' => t("Only the 'GET' method is allowed when requesting a JSON response.", $locale)], 405, $locale);
    }

    header('Location: '.$location, true, 303);
    exit;
}

if (!$isJsonRequest && '' !== $requestedLocale && $locale === $requestedLocale) {
    setcookie('locale', $locale, ['expires' => time() + 31536000, 'path' => '/', 'samesite' => 'Lax']);
    header('Location: '.$location, true, 303);
    exit;
}

if ($isJsonRequest && hasQueryValues('sizes_for')) {
    if (ShoeType::Children !== $unitType) {
        problem(['sizes_for' => 'The sizes_for parameter is only supported for Child Unit.'], 422, $locale);
    }

    $unit = $unitType->unit(strtolower(query('sizes_for')));
    if (null === $unit) {
        problem(['sizes_for' => 'The unit system is not a supported child shoe sizes type.'], 400, $locale);
    }

    $availableSizes = $unitType->converter()->availableSizes($unit);
    $sizes = array_column(iterator_to_array($availableSizes, false), 'size');
    sort($sizes, SORT_NUMERIC);
    $data = [
        'unit' => $unit->value,
        'label' => $unit->label(),
        'sizes' => $sizes,
    ];

    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: public, max-age=86400');
    echo j($data);
    exit;
}

$data = [];
$unit = null;
$size = null;
$to = null;
/** @var array<non-empty-string, non-empty-string> $errorMessages */
$errorMessages = [];
if (hasQueryValues('size', 'unit', 'to')) {
    $unit = $unitType->unit(strtolower(query('unit')));
    if (null === $unit) {
        $errorMessages['unit'] = t('Please provide a shoe-size unit (e.g., EU, US, UK, or CM).', $locale);
    }

    $size = filter_var(query('size'), FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
    if (false === $size) {
        $size = null;
        $errorMessages['size'] = t("Please provide a valid {$unit?->label()} shoe size.", $locale);
    }

    $to = $unitType->unit(strtolower(query('to')));
    if (null === $to) {
        $errorMessages['to'] = t('Please provide a shoe-size unit to convert to (e.g., EU, US, UK, or CM).', $locale);
    }
}

if ($isJsonRequest) {
    if ($unit instanceof ShoeUnit && $to instanceof ShoeUnit && null !== $size) {
        try {
            $data = convert($unit->of($size), $to, $unitType);
        } catch (Throwable $exception) {
            $errorMessages['convert'] = $exception->getMessage();
            $data = [];
        }
    }

    if ([] !== $errorMessages) {
        problem($errorMessages, array_key_exists('convert', $errorMessages) ? 422 : 400, $locale);
    }

    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: public, max-age=86400');
    echo j($data);
    exit;
}

$sizeValue = $size ?? 41;
$unitValue = $unit ?? AdultUnit::Eu;
$toValue = $to ?? AdultUnit::Uk;
if (ShoeType::Children === $unitType) {
    $sizeValue = $size ?? 23.5;
    $unitValue = $unit ?? ChildUnit::Eu;
    $toValue = $to ?? ChildUnit::Uk;
}

$labels = [];
$cases = $unitType->list();
foreach ($cases as $case) {
    $labels[$case->value] = $case->label();
}
$availableSizes = null;
if (ShoeType::Children === $unitType) {
    $availableSizes = $unitType->converter()->availableSizes($unitValue);
}

$unitTypes = [];
foreach (ShoeType::cases() as $case) {
    $unitTypes[$case->name] = $case->value;
}
?>
<!doctype html>
<html lang="<?=e($locale)?>" dir="<?=e(TRANSLATIONS[$locale]['direction'])?>">
<head>
    <meta charset="utf-8">
    <title><?=t('Shoe Size Converter', $locale)?> – EU, UK, US &amp; Mondopoint | Shoe Wizard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?=et("Convert shoe sizes between EU, UK, US men's, US women's and Mondopoint sizes with Shoe Wizard.", $locale)?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#f3f4f6">
    <meta name="color-scheme" content="light dark">
    <link rel="stylesheet" href="app.css">
    <script>
        const locale = <?=j($locale)?>;
        const translations = <?=j(TRANSLATIONS[$locale]['messages'])?>;
        const unitLabels = <?=j($labels)?>;
        const unitTypes = <?= j($unitTypes) ?>;
    </script>
    <script src="app.js" defer></script>
</head>
<body>
<main class="converter">
    <form method="GET" id="converter-form">
        <h1><strong>Shoe</strong>Wizard</h1>
        <nav aria-label="<?=et('Size type', $locale)?>" class="size-type-switcher">
            <ul>
<?php foreach (ShoeType::cases() as $case): ?>
<?php if ($unitType === $case): ?>
                <li><strong aria-current="page"><?=et($case->label(), $locale)?></strong></li>
<?php else: ?>
                <li><a href="?type=<?=e($case->value)?>"><?=et($case->label(), $locale)?></a></li>
<?php endif; ?>
<?php endforeach; ?>
            </ul>
        </nav>
        <div class="form-field">
            <label for="size"><?=et('Size', $locale)?>: </label>
<?php if (ShoeType::Children === $unitType): ?>
            <span class="input-size">
                <select name="size" id="size" dir="ltr">
<?php foreach ($availableSizes as $hsize): ?>
                    <option value="<?=e($hsize->size)?>"<?php if ($sizeValue === $hsize->size): ?> selected<?php endif; ?>><?=e($hsize->size)?></option>
<?php endforeach; ?>
                </select>
            </span>
<?php else: ?>
            <span class="input-size">
                <input type="number" name="size" id="size" min="0" max="80" step="0.5" value="<?=$sizeValue?>" required>
                <button type="button" id="size-decrease" aria-label="<?=et('Decrease shoe size', $locale)?>" aria-controls="size">−</button>
                <button type="button" id="size-increase" aria-label="<?=et('Increase shoe size', $locale)?>" aria-controls="size">+</button>
            </span>
<?php endif; ?>
        </div>
        <div class="form-field">
            <label for="unit"><?=et('From', $locale)?>: </label>
            <span class="input-size">
                <select name="unit" id="unit" required dir="ltr">
<?php foreach ($cases as $footUnit):  if ('mondopoint' !== $footUnit->value): ?>
                <option value="<?=$footUnit->value?>"<?=$footUnit === $unitValue ? 'selected' : ''?>><?=e($footUnit->label())?></option>
<?php endif; endforeach; ?>
                </select>
                <button type="button" id="swap-units" aria-label="<?=et('Swap source and target units', $locale)?>">⇅</button>
            </span>
        </div>
        <div class="form-field">
            <label for="to"><?=et('To', $locale)?>: </label>
            <span class="input-size">
                <select name="to" id="to" required dir="ltr">
<?php foreach ($cases as $footUnit):  if ('mondopoint' !== $footUnit->value): ?>
                <option value="<?=$footUnit->value?>"<?=$footUnit === $toValue ? 'selected' : ''?>><?=e($footUnit->label())?></option>
<?php endif; endforeach; ?>
                </select>
            </span>
        </div>
        <div class="form-field form-actions">
            <span></span>
            <div>
                <input type="hidden" value="<?=e($unitType->value)?>" name="type">
                <button type="submit"><?=et('Convert', $locale)?></button>
            </div>
        </div>
    </form>
    <nav class="language-switcher" aria-labelledby="language-switcher-label">
        <span id="language-switcher-label"><?=et('Language', $locale)?> :</span>
        <ul>
<?php foreach (array_keys(TRANSLATIONS) as $code): ?>
<?php if ($locale === $code): ?>
            <li><strong aria-current="page" title="<?=et(TRANSLATIONS[$code]['name'], $locale)?>"><?=e(strtoupper($code))?></strong></li>
<?php else: ?>
            <li><a href="?lang=<?=e($code)?>" hreflang="<?=e($code)?>" lang="<?=e($code)?>" data-locale="<?=e($code)?>" title="<?=et(TRANSLATIONS[$code]['name'], $locale)?>"><?=e(strtoupper($code))?></a></li>
<?php endif; ?>
 <?php endforeach; ?>
        </ul>
    </nav>
    <div id="error" class="form-error" role="alert" hidden></div>
    <div id="result" class="result" aria-live="polite" role="status" hidden></div>
</main>
<aside dir="ltr">
    <div class="theme-switcher" role="group" aria-label="<?=et('Theme', $locale)?>">
        <button type="button" data-theme="system" aria-label="<?=et('System theme', $locale)?>" title="<?=et('System theme', $locale)?>" aria-pressed="false">◐</button>
        <button type="button" data-theme="light" aria-label="<?=et('Light theme', $locale)?>" title="<?=et('Light theme', $locale)?>" aria-pressed="false">☀</button>
        <button type="button" data-theme="dark" aria-label="<?=et('Dark theme', $locale)?>" title="<?=et('Dark theme', $locale)?>" aria-pressed="false">☾</button>
    </div>
</aside>
<footer dir="ltr">
    <p>
        Background image adapted from an photo by <a href="https://unsplash.com/@mxpissioli" target="_blank">Maria Fernanda Pissioli</a>
        licensed under <a href="https://unsplash.com/license" target="_blank">Unsplash License</a>.
    </p>
</footer>
</body>
</html>

<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeteoService
{
    public function __construct(private readonly \Symfony\Contracts\HttpClient\HttpClientInterface $http) {}

    /**
     * Retourne la prévision/journalière pour une date précise (passée/prochaine)
     * @return array|null [date, label, icon, tmin, tmax, pop] ou null si indispo
     */
    public function getDailyForecast(\DateTimeInterface $date, float $lat, float $lon): ?array
    {
        $day = (clone $date)->setTime(12, 0);
        $start = $day->format('Y-m-d');
        $url = sprintf(
            'https://api.open-meteo.com/v1/forecast?latitude=%F&longitude=%F&daily=weathercode,temperature_2m_max,temperature_2m_min,precipitation_probability_mean&timezone=auto&start_date=%s&end_date=%s',
            $lat, $lon, $start, $start
        );

        $res = $this->http->request('GET', $url);
        if (200 !== $res->getStatusCode()) return null;

        $data = $res->toArray(false);
        if (empty($data['daily']['time'][0])) return null;

        $idx = 0;
        $code = (int)($data['daily']['weathercode'][$idx] ?? 0);
        $map = $this->mapWmo($code);

        return [
            'date' => new \DateTimeImmutable($data['daily']['time'][$idx]),
            'label' => $map['label'],
            'icon'  => $map['icon'],
            'tmin'  => (float)($data['daily']['temperature_2m_min'][$idx] ?? null),
            'tmax'  => (float)($data['daily']['temperature_2m_max'][$idx] ?? null),
            'pop'   => (int)($data['daily']['precipitation_probability_mean'][$idx] ?? 0),
        ];
    }

    private function mapWmo(int $code): array
    {
        return match (true) {
            $code === 0 => ['label'=>'Ensoleillé','icon'=>'sun'],
            in_array($code, [1,2,3]) => ['label'=>'Partiellement nuageux','icon'=>'sun-cloud'],
            in_array($code, [45,48]) => ['label'=>'Brouillard','icon'=>'fog'],
            in_array($code, [51,53,55,56,57]) => ['label'=>'Bruine','icon'=>'drizzle'],
            in_array($code, [61,63,65]) => ['label'=>'Pluie','icon'=>'rain'],
            in_array($code, [66,67]) => ['label'=>'Pluie verglaçante','icon'=>'rain-ice'],
            in_array($code, [71,73,75,77]) => ['label'=>'Neige','icon'=>'snow'],
            in_array($code, [80,81,82]) => ['label'=>'Averses','icon'=>'showers'],
            in_array($code, [95,96,99]) => ['label'=>'Orage','icon'=>'storm'],
            default => ['label'=>'Nuageux','icon'=>'cloud'],
        };
    }
}

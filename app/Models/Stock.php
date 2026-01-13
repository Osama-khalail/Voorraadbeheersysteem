<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'aantal',
        'laatst_aangepast_op',
        'laatst_aangepast_door',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'laatst_aangepast_door');
    }
        /**
        * Past de voorraad van een product aan met een positief of negatief aantal.
        * 
        * - Positief getal  → voorraad erbij (bijleggen)
        * - Negatief getal  → voorraad eraf (pakken)
        * 
        * Als je meer wilt afhalen dan er op voorraad is,
        * wordt er een foutmelding gegeven.
        * 
        * Na de aanpassing wordt de voorraad opgeslagen
        * en wordt er een logboekregel (StockLog) aangemaakt.
        * 
        * De functie geeft de bijgewerkte voorraad terug.
        */
    public static function adjust(int $productId, int $delta, ?int $userId = null, ?string $opmerking = null)
    {
        return DB::transaction(function() use ($productId, $delta, $userId, $opmerking) {
            $stock = self::firstOrCreate([
                'product_id' => $productId,
            ], [
                'aantal' => 0,
                'laatst_aangepast_op' => Carbon::now(),
                'laatst_aangepast_door' => $userId ?? 1,
            ]);

            if ($delta < 0) {
                $take = abs($delta);
                if (($stock->aantal ?? 0) < $take) {
                    throw new \RuntimeException('Onvoldoende voorraad');
                }
                $stock->aantal = max(0, $stock->aantal - $take);
                $wijziging = 'pakken';
                $aantal = $take;
            } else {
                $stock->aantal = ($stock->aantal ?? 0) + $delta;
                $wijziging = 'bijleggen';
                $aantal = $delta;
            }

            $stock->laatst_aangepast_op = Carbon::now();
            $stock->laatst_aangepast_door = $userId ?? $stock->laatst_aangepast_door;
            $stock->save();

            // create stock log
            StockLog::create([
                'product_id' => $productId,
                'user_id' => $userId ?? null,
                'wijziging_type' => $wijziging,
                'aantal' => $aantal,
                'opmerking' => $opmerking ?? null,
                'datumtijd' => Carbon::now(),
            ]);

            return $stock;
        });
    }

               /**
            * Past de voorraad van een product aan met een aantal ($delta).
            *
            * - Positief getal  → voorraad erbij (bijleggen)
            * - Negatief getal  → voorraad eraf (pakken)
            *
            * De functie geeft een array terug met:
            * - success    → true of false
            * - newAmount  → nieuwe voorraad (bij succes)
            * - error      → foutmelding (bij fout)
            */

    public static function adjustForProduct(int $productId, int $delta, ?int $userId = null, string $type = 'bijleggen', ?string $reason = null)
    {
        $stock = self::firstOrCreate([
            'product_id' => $productId,
        ], [
            'aantal' => 0,
            'laatst_aangepast_op' => now(),
            'laatst_aangepast_door' => $userId ?? 1,
        ]);

        if ($delta < 0) {
            $need = abs($delta);
            if ($stock->aantal < $need) {
                return ['error' => 'Onvoldoende voorraad'];
            }
        }

        $stock->aantal = max(0, $stock->aantal + $delta);
        $stock->laatst_aangepast_op = now();
        $stock->laatst_aangepast_door = $userId ?? null;
        $stock->save();

        \App\Models\StockLog::create([
            'product_id' => $productId,
            'user_id' => $userId ?? 1,
            'wijziging_type' => $type,
            'aantal' => abs($delta),
            'opmerking' => $reason ?? null,
            'datumtijd' => now(),
        ]);

        return ['success' => true, 'newAmount' => $stock->aantal];
    }

    /**
     * Set absolute stock amount for a product and log the delta.
     */
    public static function setForProduct(int $productId, int $amount, ?int $userId = null, ?string $reason = null)
    {
        $stock = self::firstOrCreate([
            'product_id' => $productId,
        ], [
            'aantal' => 0,
            'laatst_aangepast_op' => now(),
            'laatst_aangepast_door' => $userId ?? 1,
        ]);

        $delta = $amount - $stock->aantal;
        if ($delta === 0) {
            return ['success' => true, 'newAmount' => $stock->aantal];
        }

        $stock->aantal = max(0, $amount);
        $stock->laatst_aangepast_op = now();
        $stock->laatst_aangepast_door = $userId ?? null;
        $stock->save();

        $type = $delta > 0 ? 'bijleggen' : 'pakken';
        \App\Models\StockLog::create([
            'product_id' => $productId,
            'user_id' => $userId ?? 1,
            'wijziging_type' => $type,
            'aantal' => abs($delta),
            'opmerking' => $reason ?? null,
            'datumtijd' => now(),
        ]);

        return ['success' => true, 'newAmount' => $stock->aantal];
    }
}

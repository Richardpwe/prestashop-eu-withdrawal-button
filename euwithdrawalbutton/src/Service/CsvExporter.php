<?php

namespace PrestaShop\Module\EuWithdrawalButton\Service;

final class CsvExporter
{
    public function exportWithdrawals(array $rows)
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, [
            'reference',
            'status',
            'submitted_at_utc',
            'shop',
            'language',
            'customer_email',
            'order_reference',
            'id_order',
            'mail_status',
            'manual_review_required',
            'possibly_out_of_period',
        ]);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $this->escapeCsvCell($row['public_reference']),
                $row['status'],
                $row['submitted_at'],
                $row['id_shop'],
                $row['id_lang'],
                $this->escapeCsvCell($row['customer_email']),
                $this->escapeCsvCell($row['order_reference']),
                $row['id_order'],
                $row['mail_status'],
                $row['manual_review_required'],
                $row['possibly_out_of_period'],
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    private function escapeCsvCell($value)
    {
        $value = (string) $value;
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }
}


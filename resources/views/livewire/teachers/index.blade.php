<?php

use App\Models\Teacher;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Volt\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

new class extends Component {
    public function with(): array
    {
        $teachers = $this->allTeachers();
        return [
            'teachers' => $teachers,
        ];
    }

    private function generateQRCode($data): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(100),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($data));
    }

    public function generatePDF(): StreamedResponse
    {
        $teachers = $this->allTeachers();
        $pdf = Pdf::loadView('pdf', [
            'teachers' => $teachers
        ]);

        $pdf->setPaper('A4', 'portrait');

        $pdfOutput = $pdf->output();
        return response()->stream(
            function () use ($pdfOutput) {
                echo $pdfOutput;
            }, 200, [
                'Content-type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename=file.pdf'
            ]
        );
    }

    private function allTeachers(): array
    {
        $teachers = [];
        foreach (Teacher::all() as $teacher) {
            // Generate QR code and force UTF-8 encoding
            $qrcode = $this->generateQRCode($teacher->id);
            $teachers[] = [
                'data' => $teacher,
                'qrCode' => $qrcode
            ];
        }
        return $teachers;
    }

}; ?>

<div>


    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <caption
                    class="p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-white dark:text-white dark:bg-gray-800">
                <x-button class="ms-4" wire:click="generatePDF">
                    {{ __('Generate QRCode') }}
                </x-button>
            </caption>
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    #
                </th>
                <th scope="col" class="px-6 py-3">
                    Name
                </th>
                <th scope="col" class="px-6 py-3">
                    Email
                </th>
                <th scope="col" class="px-6 py-3">
                    Phone
                </th>
                <th scope="col" class="px-6 py-3">
                    QRCode
                </th>
                <th scope="col" class="px-6 py-3">
                    <span>Action</span>
                </th>
            </tr>
            </thead>
            <tbody>
            @if(count($teachers))
                @php $sno=1 @endphp
                @foreach($teachers as $teacher)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $sno++ }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $teacher['data']->name }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $teacher['data']->email }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $teacher['data']->phone }}
                        </td>
                        <td class="px-6 py-4">
                            <img src="{{ $teacher['qrCode'] }}" alt="QR Code">
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            @else
                <p>No data found</p>
            @endif


            </tbody>
        </table>
    </div>

</div>

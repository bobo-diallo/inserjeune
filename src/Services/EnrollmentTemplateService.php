<?php

namespace App\Services;

use App\Repository\ChildColumTemplateRepository;
use App\Repository\ParentColumTemplateRepository;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class EnrollmentTemplateService {
	private TranslatorInterface $translator;

	/**
	 * @param TranslatorInterface $translator
	 */
	public function __construct(TranslatorInterface $translator) {
		$this->translator = $translator;
	}

	/**
	 * Add list values to two columns (parent, child) and make child value depending on parent value selected
	 *
	 * @param Spreadsheet $spreadsheet
	 * @param ChildColumTemplateRepository $childRepository
	 * @param ParentColumTemplateRepository $parentRepository
	 * @param string $parentColumLetter
	 * @param string $childColumLetter
	 * @param string $worksheetName
	 * @return void
	 * @throws Exception
	 */
	public function createColumnMappings(
		Spreadsheet $spreadsheet,
		ChildColumTemplateRepository $childRepository,
		ParentColumTemplateRepository $parentRepository,
		string $parentColumLetter,
		string $childColumLetter,
		string $worksheetName
	): void {

		$dataSheet = $spreadsheet->getSheetByName($worksheetName);
		if ($dataSheet === null) {
			$dataSheet = new Worksheet($spreadsheet, $worksheetName);
			$spreadsheet->addSheet($dataSheet);
		}

		$parentData = $parentRepository->getTemplateData();

		$row = 1;
		$childStartRow = 1;
		$parentMappings = [];

		foreach ($parentData as $parentDatum) {

			$parentName = $parentDatum->name();

			$safeName = preg_replace('/[^A-Za-z0-9]/', '_', $parentName);
			$safeName = ltrim($safeName, '0123456789');

			$parentMappings[$parentName] = $safeName;
			$dataSheet->setCellValue($parentColumLetter . $row, $parentName);

			$childNames = $childRepository->getNameByParentId($parentDatum->id());

			if (!empty($childNames)) {
				$childEndRow = $childStartRow + count($childNames) - 1;

				foreach ($childNames as $index => $childName) {
					$dataSheet->setCellValue($childColumLetter . ($childStartRow + $index), $childName);
				}

				if (ctype_alpha(substr($safeName, 0, 1))) {
					$spreadsheet->addNamedRange(
						new NamedRange($safeName, $dataSheet, sprintf('%s%s:%s%s',
							$childColumLetter,
							$childStartRow,
							$childColumLetter,
							$childEndRow
						))
					);
				}
				$childStartRow = $childEndRow + 1;
			}
			$row++;
		}

		$mappingSheet = $spreadsheet->getSheetByName('Mappings');
		if ($mappingSheet === null) {
			$mappingSheet = new Worksheet($spreadsheet, 'Mappings');
			$spreadsheet->addSheet($mappingSheet);
			$mappingSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
		}

		$row = 1;
		foreach ($parentMappings as $originalName => $safeName) {
			$mappingSheet->setCellValue($parentColumLetter . $row, $originalName);
			$mappingSheet->setCellValue($childColumLetter . $row, $safeName);
			$row++;
		}

		$this->applyDataValidations(
			$worksheetName,
			$spreadsheet->getActiveSheet(),
			$parentColumLetter,
			$childColumLetter
		);
	}

	private function applyDataValidations(
		string $worksheetName,
		Worksheet $sheet,
		string $parentColumnLetter,
		string $childColumnLetter,
	): void {
		$parentRange = sprintf('%s!$%s$1:$%s$100', $worksheetName, $parentColumnLetter, $parentColumnLetter);
		for ($i = 2; $i <= 100; $i++) {
			$childCell = $parentColumnLetter . $i;

			$validation = $sheet->getCell($childCell)->getDataValidation();
			$validation->setType(DataValidation::TYPE_LIST);
			$validation->setErrorStyle(DataValidation::STYLE_STOP);
			$validation->setAllowBlank(false);
			$validation->setShowDropDown(true);
			$validation->setFormula1($parentRange);
			$validation->setErrorTitle($this->translator->trans('clean.error'));
			$validation->setError('The value entered is not valid.');
			$validation->setPromptTitle('Choisir dans la liste');
			$validation->setPrompt('Veuillez choisir une valeur dans la liste.');
			$sheet->getCell($childCell)->setDataValidation(clone $validation);

			$regionCell = $childColumnLetter . $i;

			$validation = $sheet->getCell($regionCell)->getDataValidation();
			$validation->setType(DataValidation::TYPE_LIST);
			$validation->setErrorStyle(DataValidation::STYLE_STOP);
			$validation->setAllowBlank(false);
			$validation->setShowDropDown(true);

			$validation->setFormula1(sprintf('INDIRECT(VLOOKUP(%s, Mappings!$%s$1:$%s$100, 2, FALSE))',
				$childCell,
				$parentColumnLetter,
				$childColumnLetter
			));
			$validation->setErrorTitle($this->translator->trans('clean.error'));
			$validation->setError('The value entered is not valid.');
			$validation->setPromptTitle('Choisir dans la liste');
			$validation->setPrompt('Veuillez choisir une valeur dans la liste.');
			$sheet->getCell($regionCell)->setDataValidation(clone $validation);
		}
	}
}

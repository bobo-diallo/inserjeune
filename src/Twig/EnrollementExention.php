<?php

namespace App\Twig;

use App\Entity\Company;
use App\Entity\PersonDegree;
use App\Tools\Tools;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EnrollementExention extends AbstractExtension {

	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function getFunctions(): array {
		return [
			new TwigFunction('add_row_person_degree', [$this, 'addRowPersonDegree'], ['is_safe' => ['html']]),
			new TwigFunction('add_row_company', [$this, 'addRowCompany'], ['is_safe' => ['html']])
		];
	}

	public function addRowPersonDegree(int $rowNumber, ?PersonDegree $personDegree, string $assetLocationIcon): string {
		$html = '';
		if ($personDegree) {
			$html .= sprintf('<tr class="enrollpersondegree" id="personDegree%d">', $rowNumber);
			$html .= sprintf('    <td class="row-actions">');
			$html .= sprintf('        <span style="display: none" id="id%d">%s</span>', $rowNumber, $personDegree->getId());
			$html .= sprintf('        <button id="edit%s" onclick="displayButton(\'edit\',%s)"><img src="%sedit_16.png" alt="edit"></button>', $rowNumber, $rowNumber, $assetLocationIcon);
			$html .= sprintf('        <button id="remove%s" onclick="displayButton(\'remove\', %s)"><img src="%sdelete.png" alt="remove"></button>', $rowNumber, $rowNumber, $assetLocationIcon);
			$html .= sprintf('        <button style="display: none"  id="save%s" onclick="displayButton(\'save\', %s)"><img src="%ssave_16.png" alt="save"></button>', $rowNumber, $rowNumber, $assetLocationIcon);
			$html .= sprintf('        <button style="display: none"  id="cancel%s" onclick="displayButton(\'cancel\', %s)"><img src="%scancel_16.png" alt="cancel"></button>', $rowNumber, $rowNumber, $assetLocationIcon);
			$html .= sprintf('    </td>');
			$html .= sprintf('    <td><input id="registrationStudentSchool%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getRegistrationStudentSchool(), $personDegree->getRegistrationStudentSchool());
			$html .= sprintf('    <td><input id="firstname%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getFirstname(), $personDegree->getFirstname());
			$html .= sprintf('    <td><input id="lastname%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getLastname(), $personDegree->getLastname());

			$birthDay = new DateTime($personDegree->getBirthDate());
			$html .= sprintf('    <td><input id="birthDate%d" type="Date" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $birthDay->format('Y-m-d'), $birthDay->format(Tools::FORMAT_FR));

			$html .= sprintf('    <td><input id="sex%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getSex(), $personDegree->getSex());
			$html .= sprintf('    <td><input id="region%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getRegion(), $personDegree->getRegion());
			$html .= sprintf('    <td><input id="city%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getAddressCity()->getName(), $personDegree->getAddressCity()->getName());
			$html .= sprintf('    <td><input id="phoneMobile1%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getPhoneMobile1(), $personDegree->getPhoneMobile1());
			$html .= sprintf('    <td><input id="phoneMobile2%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getPhoneMobile2(), $personDegree->getPhoneMobile2());
			$html .= sprintf('    <td><input id="email%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getEmail(), $personDegree->getEmail());
			$html .= sprintf('    <td><input id="degree%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getDegree(), $personDegree->getDegree());
			$html .= sprintf('    <td><input id="sectorarea%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getSectorArea(), $personDegree->getSectorArea());
			$html .= sprintf('    <td><input id="activity%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $personDegree->getActivity(), $personDegree->getActivity());
			$html .= sprintf('</tr>');
		}

		return $html;
	}

	public function addRowCompany(int $rowNumber, ?Company $company, string $assetLocationIcon): string {
		$html = '';
		if ($company) {
			$html .= sprintf('<tr class="enrollcompany" id="company%d">', $rowNumber);
			$html .= sprintf('    <td class="row-actions">');
			$html .= sprintf('        <span style="display: none" id="id%d">%s</span>', $rowNumber, $company->getId());
			$html .= sprintf('        <button id="edit%s" onclick="displayButton(\'edit\',%s)"><img src="%sedit_16.png" alt="edit"></button>', $rowNumber, $rowNumber, $assetLocationIcon);
			$html .= sprintf('        <button id="remove%s" onclick="displayButton(\'remove\', %s)"><img src="%sdelete.png" alt="remove"></button>', $rowNumber, $rowNumber, $assetLocationIcon);
			$html .= sprintf('        <button style="display: none"  id="save%s" onclick="displayButton(\'save\', %s)"><img src="%ssave_16.png" alt="save"></button>', $rowNumber, $rowNumber, $assetLocationIcon);
			$html .= sprintf('        <button style="display: none"  id="cancel%s" onclick="displayButton(\'cancel\', %s)"><img src="%scancel_16.png" alt="cancel"></button>', $rowNumber, $rowNumber, $assetLocationIcon);
			$html .= sprintf('    </td>');
			$html .= sprintf('    <td><input id="name%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $company->getName(), $company->getName());
			$html .= sprintf('    <td><input id="region%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $company->getRegion(), $company->getRegion());
			$html .= sprintf('    <td><input id="city%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $company->getCity()->getName(), $company->getCity()->getName());
			$html .= sprintf('    <td><input id="phoneStandard%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $company->getPhoneStandard(), $company->getPhoneStandard());
			$html .= sprintf('    <td><input id="email%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $company->getEmail(), $company->getEmail());
			$html .= sprintf('    <td><input id="sectorarea%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $company->getSectorArea(), $company->getSectorArea());
			$html .= sprintf('    <td><input id="legalStatus%d" style="display:none" value="%s"><p>%s</p></td>', $rowNumber, $company->getLegalStatus(), $company->getLegalStatus());
			$html .= sprintf('</tr>');
		}

		return $html;
	}
}

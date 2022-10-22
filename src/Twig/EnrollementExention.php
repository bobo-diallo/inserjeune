<?php

namespace App\Twig;

use App\Entity\Company;
use App\Entity\PersonDegree;
use App\Tools\Utils;
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
            $html .= sprintf('    <td class="tdinput"><input id="registrationStudentSchool%d" style="display:none" value="%s" placeholder="N° Identification"><p id="p_registrationStudentSchool%d" >%s</p></td>', $rowNumber, $personDegree->getRegistrationStudentSchool(), $rowNumber, $personDegree->getRegistrationStudentSchool());
            $html .= sprintf('    <td class="tdinput"><input required id="firstname%d" style="display:none" value="%s" placeholder="prénom"><p id="p_firstname%d">%s</p></td>', $rowNumber, $personDegree->getFirstname(), $rowNumber, $personDegree->getFirstname());
            $html .= sprintf('    <td class="tdinput"><input required id="lastname%d" style="display:none" value="%s" placeholder="nom"><p id="p_lastname%d">%s</p></td>', $rowNumber, $personDegree->getLastname(), $rowNumber, $personDegree->getLastname());

            $birthDay = new \DateTime($personDegree->getBirthDate());
            $html .= sprintf('    <td class="tdinput"><input required id="birthDate%d" type="Date" style="display:none" value="%s" placeholder="dd/mm/yyyy"><p id="p_birthDate%d">%s</p></td>', $rowNumber, $birthDay->format('Y-m-d'), $rowNumber, $birthDay->format(Utils::FORMAT_FR));

            $options = "";
            foreach (["un homme","une femme"] as $genre) {
                if(strcmp($genre, $personDegree->getSex())==0) { $options .= "<option selected>" . $genre . "</option>";}
                else { $options .= "<option>" . $genre . "</option>";}
            };
            $html .= sprintf('    <td class="tdselect"><p id="p_selectSex%d">%s</p><select required class="sex" style="display:none" id="selectSex%d" value="" placeholder="Sélectionnez">%s</td>',
                $rowNumber, $personDegree->getSex(), $rowNumber, $options);

            $html .= sprintf('    <td class="tdselect"><p id="p_selectRegion%d">%s</p><select required class="selectRegion" style="display:none" id="selectRegion%d" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $personDegree->getRegion()->getName(), $rowNumber, $personDegree->getRegion()->getId(), $personDegree->getRegion()->getName());

            $html .= sprintf('    <td class="tdselect"><p id="p_selectAddressCity%d">%s</p><select required class="selectCity" style="display:none" id="selectAddressCity%d" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $personDegree->getAddressCity()->getName(), $rowNumber, $personDegree->getAddressCity()->getId(), $personDegree->getAddressCity()->getName());

            $html .= sprintf('    <td class="tdinput"><input required id="phoneMobile1%d" style="display:none" value="%s" placeholder="Mobile"><p id="p_phoneMobile1%d">%s</p></td>',
                $rowNumber, $personDegree->getPhoneMobile1(), $rowNumber, $personDegree->getPhoneMobile1());

            $html .= sprintf('    <td class="tdinput"><input id="phoneMobile2%d" style="display:none" value="%s" placeholder="Mobile Parent"><p id="p_phoneMobile2%d">%s</p></td>',
                $rowNumber, $personDegree->getPhoneMobile2(), $rowNumber, $personDegree->getPhoneMobile2());

            $html .= sprintf('    <td class="tdinput"><input id="email%d" style="display:none" value="%s"><p id="p_email%d">%s</p></td>',
                $rowNumber, $personDegree->getEmail(), $rowNumber, $personDegree->getEmail());

            $html .= sprintf('    <td class="tdselect"><p id="p_selectDegree%d">%s</p><select required class="selectDegree" style="display:none" id="selectDegree%d" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $personDegree->getDegree()->getName(), $rowNumber, $personDegree->getDegree()->getId(), $personDegree->getDegree()->getName());

            $html .= sprintf('    <td class="tdselect"><p id="p_selectSectorArea%d">%s</p><select required class="selectSectorArea" style="display:none" id="selectSectorArea%d" class="selectSectorArea" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $personDegree->getSectorArea()->getName(), $rowNumber, $personDegree->getSectorArea()->getId(), $personDegree->getSectorArea()->getName());

            $html .= sprintf('    <td class="tdselect"><p id="p_selectActivity%d">%s</p><select required class="selectActivity" style="display:none" id="selectActivity%d" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $personDegree->getActivity()->getName(), $rowNumber, $personDegree->getActivity()->getId(), $personDegree->getActivity()->getName());

            $html .= sprintf('    <td class="tdinput"><p id="p_password%d">%s</p></td>', $rowNumber, $personDegree->getTemporaryPasswd());

            $html .= sprintf('</tr>');

            return $html;
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

            $html .= sprintf('    <td class="tdinput"><input required id="name%d" style="display:none" value="%s" placeholder="nom"><p id="p_name%d">%s</p></td>',
                $rowNumber, $company->getName(), $rowNumber, $company->getName());

            $html .= sprintf('    <td class="tdselect"><p id="p_selectRegion%d">%s</p><select required class="selectRegion" style="display:none" id="selectRegion%d" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $company->getRegion()->getName(), $rowNumber, $company->getRegion()->getId(), $company->getRegion()->getName());

            $html .= sprintf('    <td class="tdselect"><p id="p_selectCity%d">%s</p><select required class="selectCity" style="display:none" id="selectCity%d" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $company->getCity()->getName(), $rowNumber, $company->getCity()->getId(), $company->getCity()->getName());

            $html .= sprintf('    <td class="tdinput"><input required id="phoneStandard%d" style="display:none" value="%s" placeholder="téléphone"><p id="p_phoneStandard%d">%s</p></td>',
                $rowNumber, $company->getPhoneStandard(), $rowNumber, $company->getPhoneStandard());

            $html .= sprintf('    <td class="tdinput"><input id="email%d" style="display:none" value="%s"><p id="p_email%d">%s</p></td>',
                $rowNumber, $company->getEmail(), $rowNumber, $company->getEmail());

            $html .= sprintf('    <td class="tdselect"><p id="p_selectSectorArea%d">%s</p><select required class="selectSectorArea" style="display:none" id="selectSectorArea%d" class="selectSectorArea" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $company->getSectorArea()->getName(), $rowNumber, $company->getSectorArea()->getId(), $company->getSectorArea()->getName());

            $html .= sprintf('    <td class="tdselect"><p id="p_selectLegalStatus%d">%s</p><select required class="selectLegalStatus" style="display:none" id="selectLegalStatus%d" class="selectLegalStatus" value="" ><option selected value="%d">%s</optionselected></select></td>',
                $rowNumber, $company->getLegalStatus()->getName(), $rowNumber, $company->getLegalStatus()->getId(), $company->getLegalStatus()->getName());

            $html .= sprintf('    <td class="tdinput"><p id="p_password%d">%s</p></td>', $rowNumber, $company->getTemporaryPasswd());

            $html .= sprintf('</tr>');

            return $html;
        }

		return $html;
	}
}

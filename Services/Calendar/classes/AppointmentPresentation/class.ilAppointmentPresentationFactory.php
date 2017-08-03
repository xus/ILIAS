<?php
/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationFactory
{
	public static function getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item)
	{
		global $lng;

		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');

		//get object info
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_appointment['event']->getEntryId());
		//echo "---";
		//var_dump($cat_id);
		//$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
		$cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
		$cat_info["type"] = $cat->getType();
		$cat_info["obj_id"] = $cat->getObjId();
		//var_dump($cat_info['obj_id']);
		//var_dump(ilObject::_lookupType($cat_info['obj_id']));
		//ilUtil::printBacktrace(10); exit;

		//Milestones can be part of every type of calendar and
		// the specific data from the related object is not needed for the modal presentation.
		if($a_appointment['event']->isMilestone())
		{
			require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationMilestoneGUI.php";
			return ilAppointmentPresentationMilestoneGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
		}

		switch($cat_info['type'])
		{
			case ilCalendarCategory::TYPE_OBJ:
				$type = ilObject::_lookupType($cat_info['obj_id']);
				switch($type)
				{
					case "crs":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationCourseGUI.php";
						return ilAppointmentPresentationCourseGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
						break;
					case "grp":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGroupGUI.php";
						return ilAppointmentPresentationGroupGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
						break;
					case "sess":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationSessionGUI.php";
						return ilAppointmentPresentationSessionGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
						break;
					case "exc":
						include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationExerciseGUI.php';
						return ilAppointmentPresentationExerciseGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
						break;
					default:
						include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';
						return ilAppointmentPresentationGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item); // title, description etc... link to generic object.
				}
				break;
			case ilCalendarCategory::TYPE_USR:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationUserGUI.php";
				return ilAppointmentPresentationUserGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
				break;
			case ilCalendarCategory::TYPE_GLOBAL:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationPublicGUI.php";
				return ilAppointmentPresentationPublicGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
				break;
			case ilCalendarCategory::TYPE_CH:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationConsultationHoursGUI.php";
				return ilAppointmentPresentationConsultationHoursGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
				break;
			case ilCalendarCategory::TYPE_BOOK:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationBookingPoolGUI.php";
				return ilAppointmentPresentationBookingPoolGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
			default:
				include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';
				return ilAppointmentPresentationGUI::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);

		}
	}
}
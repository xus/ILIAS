####Survey reports with new UI panels.

- Default UI sub panel style:

    - Title underlined inside the panel with small font
    - Title uses 100% width therefore the card are under this title.
    - Card title with big padding and font size 130%
    - Card title and card content separated by grey line.
    - Background with color.

- Default UI report panel style:

    - Title outside the panel.


- subpanel modifications:

    - Title inside the panel. Now using the common H3 font grey title.
    - Title inside the bootstrap column like the question text, so the card have less top space.
    - Background color white.
    - table-striped: background-color:#f9f9f9

- Table of contents:

    - The content: New container with padding 10px.

- Card template:

    - Title: "font-height:120%;margin-bottom:0;margin-top:0;"
    - Content section: "border:none; padding-top:2px;"

- Subpanel template:

    - panel default container: background-color:white;
    - panel heading: moved to col-sm-8, background white and padding 0


####MAIN CHANGES (provisional changes, styles INLINE and the report title is hardcoded )

- We still having the original template and renderDetails just in case.

- Changes in Modules/Survey/classes/class.ilSurveyEvaluationGUI.php

    - function evaluation
    - function renderDetailsNewUI

- New templates:
    - Modules/Survey/templates/default/tpl.il_svy_svy_results_details_nUI.html
    - Modules/Survey/templates/default/tpl.svy_results_table_contents.html
    - Modules/Survey/templates/default/tpl.svy_results_details_text_answers.html
    - Modules/Survey/templates/default/tpl.svy_results_details_grid.html
    - Modules/Survey/templates/default/tpl.svy_results_details_chart.html
    - Modules/Survey/templates/default/tpl.svy_results_details_card.html

- Panel behaviour:
    - Details TPL ->  Shorter template Modules/Survey/templates/default/tpl.il_svy_svy_results_details_nUI.html
    - Table of contents -> Standard panel **without** title. Content from Modules/Survey/templates/default/tpl.svy_results_table_contents.html
    - Add this in the main panel.
    - Question info -> Subpanel with the "question title" as a title and the "question text" as legacy. This subpanel has card with "question type" as a title.
    - Grid -> Subpanel **without** title. It takes the content from  Modules/Survey/templates/default/tpl.svy_results_details_grid.html
    - Chart -> Subpanel **without** title. It takes the content from Modules/Survey/templates/default/tpl.svy_results_details_chart.html
    - Text Answers -> Subpanel with "given_answers" or "freetext_answers" as a title. Takes the content from  Modules/Survey/templates/default/tpl.svy_results_details_text_answers.html
    - Full Report -> Report panel **without** titles, it takes an array with the subpanels
    - Report panel render passed as a variable to the main template.
## Current behaviour without patch.

### scales doesn't allow 0 values. And this scales are saved with the scale value - 1 in the table "svy_answer"

#### Web workflow
**As admin:**

Repository home -> create new survey, create new page with any of this "Question type":
- Multiple Choice Question (Single Response)
- Multiple Choice Question (Multiple Response)
- Matrix Question

**In the answers section:**

- Problem:
    
    At this point, if we don not save any answer and press "back to de Survey" or leave this page without save.
    In the "svy_question" we have the records "title" and "questiontext" with NULL values and also "complete" and
    "tstamp" with value 0  (Look for services/cron which delete this rows)

- Problem:

     We can put as scale values everything without restrictions. Strings, numbers and symbols are allowed.
     After press the save button we get the success message "Modifications saved." 
     But in the database table "svy_variable" the column scale has "NULL".

- Problem:
     
     If we go to edit this answer we can see that the javascript filled the Scale with the first number available in the
     scale values. Therefore we have NULL in the DB and one new number in the edit form.
     
- Problem:
     
     Everytime one answer is added the javascript clone exactly the same row above. Therefore we have to delete the text
     and scale before write.
     
- Observation:
     
     Can't store the value "0"
     
- Observation:

    Editing the question answers. If we delete one answer with "-" button. In the table "svy_category" this answer remains available.
    this answer is perfectly deleted in "svy_variable" table.
    
        select * from svy_question;
        +-------------+-----------------+--------+----------+-------------------+-------------+-----------+------------+----------+------------+-------------+------------+---------------------+-------+
        | question_id | questiontype_fi | obj_fi | owner_fi | title             | description | author    | obligatory | complete | created    | original_id | tstamp     | questiontext        | label |
        +-------------+-----------------+--------+----------+-------------------+-------------+-----------+------------+----------+------------+-------------+------------+---------------------+-------+
        |          50 |               2 |    277 |        6 | My first question | NULL        | root user | 1          | 1        | 1475171583 |        NULL | 1475174823 | This is my question | NULL  |
        |          51 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475172649 |        NULL |          0 | NULL                | NULL  |
        |          52 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475174096 |        NULL |          0 | NULL                | NULL  |
        |          53 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475174194 |        NULL |          0 | NULL                | NULL  |
        |          54 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475174292 |        NULL |          0 | NULL                | NULL  |
        |          55 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475175261 |        NULL |          0 | NULL                | NULL  |
        +-------------+-----------------+--------+----------+-------------------+-------------+-----------+------------+----------+------------+-------------+------------+---------------------+-------+
        6 rows in set (0.00 sec)


**In the Questions page (Drag and drop section)**

Only the GUI files are affected:
- Modules/SurveyQuestionPool/classes/class.SurveySingleChoiceQuestionGUI.php 
- Modules/SurveyQuestionPool/classes/class.SurveyMultipleChoiceQuestionGUI.php 
- Modules/SurveyQuestionPool/classes/class.SurveyMatrixQuestionGUI.php 

Not affected:
- Modules/SurveyQuestionPool/classes/class.SurveySingleChoiceQuestion.php 
- Modules/SurveyQuestionPool/classes/class.SurveyMultipleChoiceQuestion.php 
- Modules/SurveyQuestionPool/classes/class.SurveyMatrixQuestion.php 

Here que can create pages, add from pool etc...

- Problem/Observation:
    
    Here we are passing to the template the scale -1. Therefore all the radiobuttons, checkboxes will have the scale value as scale -1
    Also if we need store 0 values this if statement is not valid.
    
        $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
     
    functions affected:
    
    - getParsedAnswers
    - getWorkingForm  (horizontal,vertical and combobox options)
    

## POSSIBLE CONFLICTS
In this Services I have seen "svy_variable"
- Services/Database/test/Implementations/data
- Services/Database/test/Implementations/data
- Services/LoadTest/data/usr_1000


## DATABASE TABLES

#### svy_question

Stores questions

Special columns:
- "complete" 1 or 0 depending if the user saved any data.

#### svy_qtype

Stores the question types: SingleChoice / MultipleChoice / Matrix / Metric

#### svy_variable

Stores the answers available.

Special columns:
- "sequence" determines the position in the form.
- "scale" scale value (positives or NULL. Here the scale have the real value entered, not scale -1 )
- "value1" ??? it seems to be the same as sequence but starting by 1 instead of 0 (tested deleting and adding answers)
- "value2" ??? always null?

#### svy_category

Stores all the answers saved by the user, even if this answers were deleted at any moment.

####svy_answer

Stores the answers, those that the user chooses when he takes the survey.


## EXAMPLE: Table comparation 

For the "question_fi" 50

We have 2 scales, 11 and 22:
   
    select * from svy_variable;
    +-------------+-------------+-------------+--------+--------+----------+------------+-------+-------+
    | variable_id | category_fi | question_fi | value1 | value2 | sequence | tstamp     | other | scale |
    +-------------+-------------+-------------+--------+--------+----------+------------+-------+-------+
    |         291 |         123 |          50 |      2 |   NULL |        1 | 1475174823 |     0 |    22 |
    |         290 |         122 |          50 |      1 |   NULL |        0 | 1475174823 |     0 |    11 |
    +-------------+-------------+-------------+--------+--------+----------+------------+-------+-------+
    2 rows in set (0.00 sec)

User answered but the scale is saved as value 10: (scale -1)

    select * from svy_answer;
    +-----------+-----------+-------------+-------+------------+----------+------------+
    | answer_id | active_fi | question_fi | value | textanswer | rowvalue | tstamp     |
    +-----------+-----------+-------------+-------+------------+----------+------------+
    |        54 |         3 |          50 |    10 | NULL       |        0 | 1475178660 |
    +-----------+-----------+-------------+-------+------------+----------+------------+
    1 row in set (0.00 sec)


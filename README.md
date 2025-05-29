_# About Repo
Fresh laravel repo with all task related to ARPM task.

---

# Task One solution thought process

## Creating Data table
1. I added a formula to generate column header
    ```
    =ARRAY_CONSTRAIN(ARRAYFORMULA("Week " & SEQUENCE(1,52)), 1, 52)
    ```
2. I added a formula to generate row header
    ```
    =ARRAY_CONSTRAIN(ARRAYFORMULA("Individual " & SEQUENCE(10)), 10, 1)
    ```
3. For **Data Table** I used a formula to generate random numbers between 0 and 1 . (10 rows, 52 columns)
   ```
   =RANDARRAY(10, 52)
   ```

## Creating Cumulative Data Sum table
1. I added a formula to generate column header
    ```
    =ARRAY_CONSTRAIN(ARRAYFORMULA("Week " & SEQUENCE(1,52)), 1, 52)
    ```
2. I added a formula to generate row header
    ```
    =ARRAY_CONSTRAIN(ARRAYFORMULA("Individual " & SEQUENCE(10)), 10, 1)
    ```
3. For **Cumulative Data Sum** First I filled cell B16,
    Then added a formula on cell C16
   ```
   =B16+C3
   ```
   Then I just dragged and implemented formula till the end of row and column
4. 

## Creating The line graph.
1. I highlighted the data frame from : A15:BA25
2. Clicked on Insert > Chart 
3. On the "Setup" section, I updated the default chart to line chart
4. Clicked on "Switch rows/ columns" checkbox
5. Clicked on "Use column A as header"
6. Updated/Added labels/titles from "Customize" section

# Output:
- Offline xlsx: [APRM - Cummilative Chart Solution By Momik.xlsx](public/APRM%20-%20Cummilative%20Chart%20Solution%20By%20Momik.xlsx)
- Google sheet: https://docs.google.com/spreadsheets/d/1eaY2Q-NjLw9pIEigB2Zft7T-P2YykavYBFodG5s-n84/edit?usp=sharing

---

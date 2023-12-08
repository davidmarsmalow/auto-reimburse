Flow of Application:
1. Upload an Image to the API
2. Process the Image and read the content using OCR to get the data (ex: amount, date).
3. Insert a new row into the database.
4. Generate a document from the date range provided in request API.
5. Query the data between the range.
6. Send the document generated into the response API.

Notes:
-- Step 1-3 is correctly working on some of the screenshot given (QR BCA, QR Gopay) and still needs further development from different format image.
-- Step 4-6 is still on progress.

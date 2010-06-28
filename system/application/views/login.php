<div id="content" class="content">
   <?php if (isset($error)){
        echo "<p> $error</p>";
    }?>
    <?php print form_open('increaseintellect/login')?>
    <br><label for="userid">User ID:</label><input type="text" name="UserID"/>
    <label for"password">Password:</label><input type="password" name="Password"/><br><br>
    <input type="submit" value="Log In" name="submLogin" />
    </form>
    
</div>
</body>
</html>
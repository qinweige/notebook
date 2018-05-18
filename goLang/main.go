package main

import (
	"API/testing/utils"
	"crypto/sha512"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"regexp"
	"sort"
	"strconv"
	"strings"
	"time"
)

var p = fmt.Println

func main() {
	timeFormat()
	stringFunc()
	regExpFunc()
	jsonFunc()
	shaFunc()
	sortFunc()
}

func timeFormat() {
	t := time.Now()
	p(t)                               // 2018-05-17 10:37:13.3461402 +0800 CST m=+0.001999801
	p(t.Year(), t.Month(), t.Hour())   //2018 May 10
	p(t.Format(time.RFC3339))          //2018-05-17T10:37:13+08:00
	p(t.Format("Mon Jan 02 18:00:00")) // Thu May 17 58:00:00
}

func stringFunc() {
	//Convert string to string array
	stringSample := "abcdefghigk"
	stringSlice := strings.Split(stringSample, "")
	p(stringSlice) // [a b c d e f g]

	//Format float | Int to string
	numberF := 3.1400
	numberI := 10
	stringF := strconv.FormatFloat(numberF, 'f', -1, 64)
	stringI := strconv.Itoa(numberI)
	p(stringF) // "3.14"
	p(stringI) // "10"

	//String to float | int
	s2f, _ := strconv.ParseFloat("1.234", 64)
	s2i, _ := strconv.ParseInt("100", 10, 64)
	p(s2f, s2i)
}

func regExpFunc() {
	//Normal regular match
	match, _ := regexp.MatchString("p([a-z]+)ch", "peach")
	p(match) //true

	//Compile the match string first, then do match can return the matched string
	r, _ := regexp.Compile("p([a-z]+)ch")
	p(r.FindString("peach"))      // peach
	p(r.FindStringIndex("peach")) //[0 5]

	//Can find all the matched string slice
	stringSlice := r.FindAllString("peach, punch, pppch", -1)
	stringIndex := r.FindAllIndex([]byte("Peach, ppppch, punch"), -1)
	p(stringSlice) //[peach punch pppch]
	p(stringIndex) //[[7 13] [15 20]]

	//Can replace the string
	replacedString := r.ReplaceAllString("peach is good", "")
	p(replacedString) // is good
}

func jsonFunc() {
	//Basically, we can marshal everything
	sliceJSON, _ := json.Marshal([]string{"apple", "banana"})
	p(string(sliceJSON)) // ["apple","banana"]

	//Unmarshal to map[string]interface{}, but need to case after unmarshal
	var mapUnmarshal map[string]interface{}
	json.Unmarshal([]byte(`{"num":6.6, "strs":["abc", "def"]}`), &mapUnmarshal)
	p(mapUnmarshal["num"])                     // float64 6.6
	p(mapUnmarshal["strs"].([]interface{})[0]) // abc
}

func shaFunc() {
	encryString := "hash the string"
	sha := sha512.New()
	sha.Write([]byte(encryString))
	signature := sha.Sum(nil)
	p(signature)                     // [236 238 123 151 255 ..... ] []byte
	p(hex.EncodeToString(signature)) // ecee7b971a9d95a9eb8334334631018d5695208d6d5003c0e8ffe0b
}

func sortFunc() {
	//Normal sort for slice, Ints, Strings, Float64s
	intSlice := []int{1, 2, 5, 3, 6}
	sort.Ints(intSlice)
	p(intSlice) // [1 2 3 5 6]
	sort.Sort(sort.Reverse(sort.IntSlice(intSlice)))
	p(intSlice) // [6 5 3 2 1]

	//Create type sort, need to implement len() int, swap(i,j int), less(i,j int) bool
	strIntMap := map[string]int{"keyword1": 1, "keyword2": 2, }
	sortedStruct := utils.RankByWordCount(strIntMap)
	p(sortedStruct) //[{keyword2 2} {keyword1 1}]
}

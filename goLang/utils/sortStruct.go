package utils

import (
	"sort"
)

//RankByWordCount will rank a map and return struct
func RankByWordCount(wordFrequencies map[string]int) PairList {
	pl := make(PairList, len(wordFrequencies))
	i := 0
	for k, v := range wordFrequencies {
		pl[i] = Pair{k, v}
		i++
	}
	sort.Sort(sort.Reverse(pl))
	return pl
}

//Pair is the structure like a map
type Pair struct {
	Key   string
	Value int
}

//PairList is the slice of a structure
type PairList []Pair

func (p PairList) Len() int           { return len(p) }
func (p PairList) Less(i, j int) bool { return p[i].Value < p[j].Value }
func (p PairList) Swap(i, j int)      { p[i], p[j] = p[j], p[i] }


	/*
		19 func (s ByLength) Len() int {
		20     return len(s)
		21 }
		22 func (s ByLength) Swap(i, j int) {
		23     s[i], s[j] = s[j], s[i]
		24 }
		25 func (s ByLength) Less(i, j int) bool {
		26     return len(s[i]) < len(s[j])
		27 }
	*/

	/*
	func main() {
    m := map[string]int{
        "something": 10,
        "yo":        20,
        "blah":      20,
    }

    type kv struct {
        Key   string
        Value int
    }

    var ss []kv
    for k, v := range m {
        ss = append(ss, kv{k, v})
    }

    sort.Slice(ss, func(i, j int) bool {
        return ss[i].Value > ss[j].Value
    })

    for _, kv := range ss {
        fmt.Printf("%s, %d\n", kv.Key, kv.Value)
    }
}
*/
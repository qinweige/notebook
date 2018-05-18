package algo

import (
	"strings"
)

//Compare string one by one
//Not good, because the time coplexity is array length * first string length
func longestCommonPrefix(strs []string) string {
	if len(strs) == 0 {
		return ""
	}
	var pre string
	if len(strs[0]) > 0 {
		loop: 
		for i := 1; i <= len(strs[0]); i++ {
			pre = strs[0][:i]
			for _, value := range strs {
				if !strings.HasPrefix(value, pre) {
					break loop
				}
			}
			if i == len(strs[0]) {
				return pre
			}
		}
		return pre[:len(pre)-1]
	}
	return ""
}

func longestCommonPrefixV2(strs []string) string {
	if len(strs) == 0 {
		return ""
	}

	prefix := strs[0]
	for _, value := range strs {
		for !strings.HasPrefix(value, prefix){
			prefix = prefix[:len(prefix)-1]
			if prefix == "" {
				return ""
			}
		}
	}
	return prefix
}